<?php

use App\Http\Controllers\Api\DocumentRequirementController;
use App\Http\Controllers\Api\DocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'tenant.active'])->prefix('api')->group(function () {
    
    // Document Requirements
    Route::get('/requirements', [DocumentRequirementController::class, 'index']);
    Route::post('/requirements', [DocumentRequirementController::class, 'store']);
    Route::put('/requirements/{id}', [DocumentRequirementController::class, 'update']);
    Route::delete('/requirements/{id}', [DocumentRequirementController::class, 'destroy']);

    // Documents
    Route::post('/documents/upload', [DocumentController::class, 'upload']);
    Route::get('/documents/student', [DocumentController::class, 'getStudentDocuments']);
    Route::post('/documents/review', [DocumentController::class, 'review']);
});
