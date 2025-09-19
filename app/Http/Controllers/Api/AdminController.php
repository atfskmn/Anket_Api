<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('admin');
    }

    public function surveys(Request $request)
    {
        $surveys = Survey::with('creator')
            ->withCount('responses')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($surveys);
    }

    public function responses(Request $request)
    {
        $responses = Response::with('survey', 'answers.question', 'answers.option')
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        return response()->json($responses);
    }

    public function analytics()
    {
        $totalSurveys = Survey::count();
        $totalResponses = Response::count();
        $totalUsers = User::count();
        $activeSurveys = Survey::where('is_active', true)->count();

        $recentSurveys = Survey::withCount('responses')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $popularSurveys = Survey::withCount('responses')
            ->orderBy('response_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_surveys' => $totalSurveys,
            'total_responses' => $totalResponses,
            'total_users' => $totalUsers,
            'active_surveys' => $activeSurveys,
            'recent_surveys' => $recentSurveys,
            'popular_surveys' => $popularSurveys,
        ]);
    }

    public function updateSurveyStatus(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);

        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $survey->update([
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'message' => 'Survey status updated successfully',
            'survey' => $survey
        ]);
    }

    public function deleteSurvey($id)
    {
        $survey = Survey::findOrFail($id);
        $survey->delete();

        return response()->json([
            'message' => 'Survey deleted successfully'
        ]);
    }
}
