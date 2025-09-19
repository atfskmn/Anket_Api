<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    public function store(Request $request, $surveyId)
    {
        $survey = Survey::where('is_public', true)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->findOrFail($surveyId);

        // Maksimum cevap kontrolü
        if ($survey->max_responses && $survey->response_count >= $survey->max_responses) {
            return response()->json([
                'message' => 'This survey has reached the maximum number of responses'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'respondent_name' => $survey->allow_anonymous ? 'nullable' : 'required|string|max:100',
            'respondent_email' => $survey->allow_anonymous ? 'nullable|email' : 'required|email|max:255',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_id' => 'required_if:question_type,multiple_choice,single_choice|exists:question_options,id',
            'answers.*.answer_text' => 'required_if:question_type,text,textarea|string',
            'answers.*.rating_value' => 'required_if:question_type,rating|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Zorunlu soruları kontrol et
        $requiredQuestions = $survey->questions()->where('is_required', true)->pluck('id');
        $answeredQuestions = collect($request->answers)->pluck('question_id');

        $missingQuestions = $requiredQuestions->diff($answeredQuestions);
        if ($missingQuestions->count() > 0) {
            return response()->json([
                'message' => 'Missing required questions',
                'missing_questions' => $missingQuestions
            ], 422);
        }

        // Cevabı kaydet
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'respondent_name' => $request->respondent_name,
            'respondent_email' => $request->respondent_email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        // Cevapları kaydet
        foreach ($request->answers as $answerData) {
            Answer::create([
                'response_id' => $response->id,
                'question_id' => $answerData['question_id'],
                'option_id' => $answerData['option_id'] ?? null,
                'answer_text' => $answerData['answer_text'] ?? null,
                'rating_value' => $answerData['rating_value'] ?? null,
            ]);
        }

        // Anketin cevap sayısını güncelle
        $survey->increment('response_count');

        return response()->json([
            'message' => 'Response submitted successfully',
            'response_id' => $response->id
        ], 201);
    }

    public function results($surveyId)
    {
        $survey = Survey::with(['questions.options', 'responses.answers'])
            ->where('is_public', true)
            ->findOrFail($surveyId);

        // Sonuçları analiz et ve döndür
        $results = $this->analyzeResults($survey);

        return response()->json([
            'survey' => $survey,
            'results' => $results,
            'response_count' => $survey->response_count
        ]);
    }

    private function analyzeResults($survey)
    {
        $results = [];

        foreach ($survey->questions as $question) {
            $questionResults = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'total_answers' => $question->answers->count(),
            ];

            switch ($question->question_type) {
                case 'multiple_choice':
                case 'single_choice':
                    $questionResults['options'] = [];
                    foreach ($question->options as $option) {
                        $count = $question->answers->where('option_id', $option->id)->count();
                        $percentage = $questionResults['total_answers'] > 0
                            ? round(($count / $questionResults['total_answers']) * 100, 2)
                            : 0;

                        $questionResults['options'][] = [
                            'option_id' => $option->id,
                            'option_text' => $option->option_text,
                            'count' => $count,
                            'percentage' => $percentage
                        ];
                    }
                    break;

                case 'rating':
                    $questionResults['average_rating'] = $question->answers->avg('rating_value');
                    $questionResults['rating_distribution'] = [
                        1 => $question->answers->where('rating_value', 1)->count(),
                        2 => $question->answers->where('rating_value', 2)->count(),
                        3 => $question->answers->where('rating_value', 3)->count(),
                        4 => $question->answers->where('rating_value', 4)->count(),
                        5 => $question->answers->where('rating_value', 5)->count(),
                    ];
                    break;

                case 'yes_no':
                    $yesCount = $question->answers->where('answer_text', 'yes')->count();
                    $noCount = $question->answers->where('answer_text', 'no')->count();

                    $questionResults['yes_count'] = $yesCount;
                    $questionResults['no_count'] = $noCount;
                    $questionResults['yes_percentage'] = $questionResults['total_answers'] > 0
                        ? round(($yesCount / $questionResults['total_answers']) * 100, 2)
                        : 0;
                    $questionResults['no_percentage'] = $questionResults['total_answers'] > 0
                        ? round(($noCount / $questionResults['total_answers']) * 100, 2)
                        : 0;
                    break;

                case 'text':
                case 'textarea':
                    $questionResults['answers'] = $question->answers->pluck('answer_text');
                    break;
            }

            $results[] = $questionResults;
        }

        return $results;
    }
}
