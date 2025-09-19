<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Controllers\Api\AdminController;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/surveys/public', [SurveyController::class, 'publicSurveys']);
Route::get('/surveys/{id}/public', [SurveyController::class, 'getPublicSurvey']);
Route::post('/surveys/{id}/responses', [ResponseController::class, 'store']);
Route::get('/surveys/{id}/results', [ResponseController::class, 'results']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Survey routes
    Route::get('/surveys', [SurveyController::class, 'index']);
    Route::post('/surveys', [SurveyController::class, 'store']);
    Route::get('/surveys/{id}', [SurveyController::class, 'show']);
    Route::put('/surveys/{id}', [SurveyController::class, 'update']);
    Route::delete('/surveys/{id}', [SurveyController::class, 'destroy']);

    // Question routes
    Route::post('/surveys/{surveyId}/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

    // Analytics
    Route::get('/surveys/{id}/analytics', [ResponseController::class, 'analytics']);

    // Admin routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/surveys', [AdminController::class, 'surveys']);
        Route::get('/responses', [AdminController::class, 'responses']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
        Route::put('/surveys/{id}/status', [AdminController::class, 'updateSurveyStatus']);
        Route::delete('/surveys/{id}', [AdminController::class, 'deleteSurvey']);
    });
});

// Web route for testing - ana sayfa için
Route::get('/', function () {
    return response()->json([
        'message' => 'Survey API is running',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /api/auth/register' => 'User registration',
            'POST /api/auth/login' => 'User login',
            'GET /api/surveys/public' => 'Get public surveys',
            // Diğer endpoint'ler...
        ]
    ]);
});
