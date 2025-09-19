<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $surveys = Survey::where('created_by', $user->id)
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($surveys);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'allow_anonymous' => 'boolean',
            'max_responses' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active ?? true,
            'is_public' => $request->is_public ?? true,
            'allow_anonymous' => $request->allow_anonymous ?? false,
            'max_responses' => $request->max_responses,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Survey created successfully',
            'survey' => $survey
        ], 201);
    }

    public function show($id)
    {
        $survey = Survey::with('questions.options')->findOrFail($id);

        return response()->json($survey);
    }

    public function update(Request $request, $id)
    {
        $survey = Survey::where('created_by', $request->user()->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'allow_anonymous' => 'boolean',
            'max_responses' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $survey->update($request->all());

        return response()->json([
            'message' => 'Survey updated successfully',
            'survey' => $survey
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $survey = Survey::where('created_by', $request->user()->id)
            ->findOrFail($id);

        $survey->delete();

        return response()->json([
            'message' => 'Survey deleted successfully'
        ]);
    }

    public function publicSurveys()
    {
        $surveys = Survey::where('is_public', true)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($surveys);
    }

    public function getPublicSurvey($id)
    {
        $survey = Survey::with(['questions' => function($query) {
                $query->orderBy('sort_order');
            }, 'questions.options'])
            ->where('is_public', true)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->findOrFail($id);

        return response()->json($survey);
    }
}
