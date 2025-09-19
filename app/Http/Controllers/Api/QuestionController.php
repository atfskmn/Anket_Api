<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function store(Request $request, $surveyId)
    {
        $survey = Survey::where('created_by', $request->user()->id)
            ->findOrFail($surveyId);

        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,single_choice,text,textarea,rating,yes_no',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
            'help_text' => 'nullable|string',
            'options' => 'required_if:question_type,multiple_choice,single_choice|array',
            'options.*.option_text' => 'required_with:options|string',
            'options.*.is_other' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $question = Question::create([
            'survey_id' => $survey->id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'is_required' => $request->is_required ?? false,
            'sort_order' => $request->sort_order ?? 0,
            'help_text' => $request->help_text,
        ]);

        // Seçenekleri ekle (eğer varsa)
        if (in_array($request->question_type, ['multiple_choice', 'single_choice']) && $request->has('options')) {
            foreach ($request->options as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['option_text'],
                    'sort_order' => $index,
                    'is_other' => $optionData['is_other'] ?? false,
                ]);
            }
        }

        return response()->json([
            'message' => 'Question added successfully',
            'question' => $question->load('options')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $question = Question::whereHas('survey', function($query) use ($request) {
                $query->where('created_by', $request->user()->id);
            })
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,single_choice,text,textarea,rating,yes_no',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
            'help_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $question->update($request->all());

        return response()->json([
            'message' => 'Question updated successfully',
            'question' => $question
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $question = Question::whereHas('survey', function($query) use ($request) {
                $query->where('created_by', $request->user()->id);
            })
            ->findOrFail($id);

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully'
        ]);
    }
}
