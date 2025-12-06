<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Public\AboutController;
use App\Http\Controllers\Api\Public\AnnouncementsController;
use App\Http\Controllers\Api\Public\ContactController;
use App\Http\Controllers\Api\Public\EstablishmentsController;
use App\Http\Controllers\Api\Public\FilieresController;
use App\Http\Controllers\Api\Public\ModulesController;
use App\Http\Controllers\Api\Public\SpecialitiesController;
use App\Http\Controllers\Api\Student\StudentDashboardController;
use App\Http\Controllers\Api\Student\StudentNotesController;
use App\Http\Controllers\Api\Student\StudentProfileController;
use App\Http\Controllers\Api\Admin\AdminAnnouncementsController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminEstablishmentsController;
use App\Http\Controllers\Api\Admin\AdminFilieresController;
use App\Http\Controllers\Api\Admin\AdminModulesController;
use App\Http\Controllers\Api\Admin\AdminNotesController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\AdminSpecialitiesController;
use App\Http\Controllers\Api\Admin\AdminStudentsController;
use App\Http\Controllers\Api\Admin\AdminYearsController;
use App\Http\Controllers\Api\Admin\AdminSemestersController;
use App\Http\Controllers\Api\Admin\AdminGroupsController;
use App\Http\Controllers\Api\Admin\AdminPlanningsController;
use App\Http\Controllers\Api\Admin\AdminPlanningItemsController;
use App\Http\Controllers\Api\Student\StudentScheduleController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsStudent;
use Illuminate\Support\Facades\Route;

// Authentication routes (no auth required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Public routes (no auth required)
Route::prefix('public')->group(function () {
    Route::get('/filieres', [FilieresController::class, 'index']);
    Route::get('/filieres/{id}', [FilieresController::class, 'show']);
    Route::get('/specialites', [SpecialitiesController::class, 'index']);
    Route::get('/specialites/{id}', [SpecialitiesController::class, 'show']);
    Route::get('/modules', [ModulesController::class, 'index']);
    Route::get('/modules/{id}', [ModulesController::class, 'show']);
    Route::get('/establishments', [EstablishmentsController::class, 'index']);
    Route::get('/announcements', [AnnouncementsController::class, 'index']);
    Route::get('/about', [AboutController::class, 'index']);
    Route::get('/contact', [ContactController::class, 'index']);
});

// Student routes (requires student auth)
Route::prefix('student')->middleware(['auth:sanctum', EnsureUserIsStudent::class])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index']);
    Route::get('/modules', [StudentDashboardController::class, 'modules']);
    Route::get('/schedule', [StudentScheduleController::class, 'index']);
    Route::get('/notes', [StudentNotesController::class, 'index']);
    Route::get('/notes/{id}', [StudentNotesController::class, 'show']);
    Route::get('/notes/{id}/preview', [StudentNotesController::class, 'preview']);
    Route::get('/notes/{id}/download', [StudentNotesController::class, 'download']);
    Route::get('/notes/{id}/serve', [StudentNotesController::class, 'serve'])->name('api.student.notes.serve');
    Route::get('/profile', [StudentProfileController::class, 'show']);
    Route::put('/profile', [StudentProfileController::class, 'update']);
    Route::put('/change-password', [StudentProfileController::class, 'changePassword']);
});

// Admin routes (requires admin auth)
Route::prefix('admin')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/analytics/downloads', [AdminDashboardController::class, 'downloadAnalytics']);
    Route::get('/analytics/students', [AdminDashboardController::class, 'studentAnalytics']);
    Route::get('/audit-logs', [AdminDashboardController::class, 'auditLogs']);

    // Student Management
    Route::get('/students', [AdminStudentsController::class, 'index']);
    Route::post('/students', [AdminStudentsController::class, 'store']);
    Route::get('/students/{id}', [AdminStudentsController::class, 'show']);
    Route::put('/students/{id}', [AdminStudentsController::class, 'update']);
    Route::delete('/students/{id}', [AdminStudentsController::class, 'destroy']);
    Route::post('/students/{id}/reset-password', [AdminStudentsController::class, 'resetPassword']);
    Route::post('/students/{id}/assign-modules', [AdminStudentsController::class, 'assignModules']);
    Route::get('/students/{id}/activity', [AdminStudentsController::class, 'activity']);

    // Content Management - Filieres
    Route::get('/filieres', [AdminFilieresController::class, 'index']);
    Route::post('/filieres', [AdminFilieresController::class, 'store']);
    Route::put('/filieres/{id}', [AdminFilieresController::class, 'update']);
    Route::delete('/filieres/{id}', [AdminFilieresController::class, 'destroy']);

    // Content Management - Specialities
    Route::get('/specialites', [AdminSpecialitiesController::class, 'index']);
    Route::post('/specialites', [AdminSpecialitiesController::class, 'store']);
    Route::put('/specialites/{id}', [AdminSpecialitiesController::class, 'update']);
    Route::delete('/specialites/{id}', [AdminSpecialitiesController::class, 'destroy']);

    // Content Management - Modules
    Route::get('/modules', [AdminModulesController::class, 'index']);
    Route::post('/modules', [AdminModulesController::class, 'store']);
    Route::put('/modules/{id}', [AdminModulesController::class, 'update']);
    Route::delete('/modules/{id}', [AdminModulesController::class, 'destroy']);

    // Academic Structure Management - Years
    Route::get('/years', [AdminYearsController::class, 'index']);
    Route::post('/years', [AdminYearsController::class, 'store']);
    Route::get('/years/{id}', [AdminYearsController::class, 'show']);
    Route::put('/years/{id}', [AdminYearsController::class, 'update']);
    Route::delete('/years/{id}', [AdminYearsController::class, 'destroy']);

    // Academic Structure Management - Semesters
    Route::get('/semesters', [AdminSemestersController::class, 'index']);
    Route::post('/semesters', [AdminSemestersController::class, 'store']);
    Route::get('/semesters/{id}', [AdminSemestersController::class, 'show']);
    Route::put('/semesters/{id}', [AdminSemestersController::class, 'update']);
    Route::delete('/semesters/{id}', [AdminSemestersController::class, 'destroy']);

    // Academic Structure Management - Groups
    Route::get('/groups', [AdminGroupsController::class, 'index']);
    Route::post('/groups', [AdminGroupsController::class, 'store']);
    Route::get('/groups/{id}', [AdminGroupsController::class, 'show']);
    Route::put('/groups/{id}', [AdminGroupsController::class, 'update']);
    Route::delete('/groups/{id}', [AdminGroupsController::class, 'destroy']);

    // Planning Management
    Route::get('/plannings', [AdminPlanningsController::class, 'index']);
    Route::post('/plannings', [AdminPlanningsController::class, 'store']);
    Route::get('/plannings/{id}', [AdminPlanningsController::class, 'show']);
    Route::put('/plannings/{id}', [AdminPlanningsController::class, 'update']);
    Route::delete('/plannings/{id}', [AdminPlanningsController::class, 'destroy']);
    Route::post('/plannings/{id}/publish', [AdminPlanningsController::class, 'publish']);
    Route::post('/plannings/{id}/unpublish', [AdminPlanningsController::class, 'unpublish']);

    // Planning Items Management
    Route::get('/plannings/{planningId}/items', [AdminPlanningItemsController::class, 'index']);
    Route::post('/plannings/{planningId}/items', [AdminPlanningItemsController::class, 'store']);
    Route::get('/planning-items/{id}', [AdminPlanningItemsController::class, 'show']);
    Route::put('/planning-items/{id}', [AdminPlanningItemsController::class, 'update']);
    Route::delete('/planning-items/{id}', [AdminPlanningItemsController::class, 'destroy']);

    // Content Management - Establishments
    Route::get('/establishments', [AdminEstablishmentsController::class, 'index']);
    Route::post('/establishments', [AdminEstablishmentsController::class, 'store']);
    Route::put('/establishments/{id}', [AdminEstablishmentsController::class, 'update']);
    Route::delete('/establishments/{id}', [AdminEstablishmentsController::class, 'destroy']);

    // Notes Management
    Route::get('/notes', [AdminNotesController::class, 'index']);
    Route::post('/notes', [AdminNotesController::class, 'store']);
    Route::post('/notes/bulk-upload', [AdminNotesController::class, 'bulkUpload']);
    Route::get('/notes/{id}', [AdminNotesController::class, 'show']);
    Route::put('/notes/{id}', [AdminNotesController::class, 'update']);
    Route::delete('/notes/{id}', [AdminNotesController::class, 'destroy']);
    Route::post('/notes/{id}/assign', [AdminNotesController::class, 'assign']);
    Route::get('/notes/{id}/stats', [AdminNotesController::class, 'stats']);

    // Announcements
    Route::get('/announcements', [AdminAnnouncementsController::class, 'index']);
    Route::post('/announcements', [AdminAnnouncementsController::class, 'store']);
    Route::put('/announcements/{id}', [AdminAnnouncementsController::class, 'update']);
    Route::delete('/announcements/{id}', [AdminAnnouncementsController::class, 'destroy']);

    // Profile Management
    Route::get('/profile', [AdminProfileController::class, 'show']);
    Route::put('/profile', [AdminProfileController::class, 'update']);
    Route::put('/change-password', [AdminProfileController::class, 'changePassword']);
});

