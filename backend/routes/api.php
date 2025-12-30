<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\UpdateRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Public routes (no authentication required)
Route::get('/slider-data', [AdminController::class, 'getSliderData']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    // Family members - view for all authenticated users
    Route::get('/family-members', [FamilyMemberController::class, 'index']);
    Route::get('/family-members/{id}', [FamilyMemberController::class, 'show']);
    Route::get('/family-tree', [FamilyMemberController::class, 'tree']);

    // Self-update for authenticated users (members can edit their own profile)
    Route::put('/family-members/self', [FamilyMemberController::class, 'updateSelf']);
    Route::patch('/family-members/self', [FamilyMemberController::class, 'updateSelf']);

    // Admin-only family member operations
    Route::middleware('admin')->group(function () {
        Route::post('/family-members', [FamilyMemberController::class, 'store']);
        Route::put('/family-members/{id}', [FamilyMemberController::class, 'update']);
        Route::patch('/family-members/{id}', [FamilyMemberController::class, 'update']);
        Route::post('/family-members/{id}/convert-to-user', [FamilyMemberController::class, 'convertToUser']);
        Route::delete('/family-members/{id}', [FamilyMemberController::class, 'destroy']);
        Route::post('/fix-spouse-relationships', [FamilyMemberController::class, 'fixSpouseRelationships']);
    });

    // Update requests - view for all, admin operations require admin role
    Route::get('/update-requests', [UpdateRequestController::class, 'index']);
    Route::get('/update-requests/{id}', [UpdateRequestController::class, 'show']);
    Route::post('/update-requests', [UpdateRequestController::class, 'store']);

    // Admin-only update request operations
    Route::middleware('admin')->group(function () {
        Route::post('/update-requests/{id}/approve', [UpdateRequestController::class, 'approve']);
        Route::post('/update-requests/{id}/reject', [UpdateRequestController::class, 'reject']);
        Route::put('/update-requests/{id}', [UpdateRequestController::class, 'update']);
        Route::delete('/update-requests/{id}', [UpdateRequestController::class, 'destroy']);
    });

    // Admin management routes (admin only)
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('users', AdminController::class);
        Route::post('/upload-slider-image', [AdminController::class, 'uploadSliderImage']);
        Route::post('/save-slider-data', [AdminController::class, 'saveSliderData']);
    });
});
