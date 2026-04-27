<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DocumentReviewController;
use App\Http\Controllers\Admin\RemarkController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StatusUpdateController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportUpdatesController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\DocumentController as StudentDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/tenant/logo', [SettingsController::class, 'logo'])->name('tenant.logo');

Route::middleware(['auth', 'tenant.active'])->group(function () {
    Route::get('/dashboard', function () {
        $role = auth()->user()?->role;

        return match ($role) {
            'student' => redirect()->route('student.dashboard'),
            'admission' => redirect()->route('admission.dashboard'),
            'department' => redirect()->route('department.dashboard'),
            'faculty' => redirect()->route('faculty.dashboard'),
            default => redirect()->route('admin.dashboard'),
        };
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin,tenant_admin,admission,department,faculty')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware(['permission:manage_students', 'module:students'])->group(function () {
            Route::post('/students/link-user/{user}', [StudentController::class, 'linkUser'])->name('students.link-user');
            Route::resource('students', StudentController::class)->except(['create', 'store']);
            Route::post('/students/{student}/remarks', [RemarkController::class, 'store'])->name('remarks.store');
            Route::delete('/remarks/{remark}', [RemarkController::class, 'destroy'])->name('remarks.destroy');
        });

        Route::middleware(['permission:manage_status_updates', 'module:status_monitoring'])->group(function () {
            Route::get('/status-monitoring', [StatusUpdateController::class, 'index'])->name('status-updates.index');
            Route::post('/students/{student}/update-status', [StatusUpdateController::class, 'store'])->name('status-updates.store');
            Route::post('/status-updates/{statusUpdate}/approve', [StatusUpdateController::class, 'approve'])->name('status-updates.approve');
            Route::post('/status-updates/{statusUpdate}/reject', [StatusUpdateController::class, 'reject'])->name('status-updates.reject');
        });

        Route::middleware(['permission:review_documents', 'module:documents'])->group(function () {
            Route::get('/document-reviews', [DocumentReviewController::class, 'index'])->name('documents.index');
            Route::get('/documents/{document}/view', [DocumentReviewController::class, 'view'])->name('documents.view');
            Route::post('/documents/{document}/approve', [DocumentReviewController::class, 'approve'])->name('documents.approve');
            Route::post('/documents/{document}/reject', [DocumentReviewController::class, 'reject'])->name('documents.reject');
        });

        Route::middleware(['permission:view_reports', 'module:reports'])->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
            Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        });

        Route::middleware(['permission:manage_users', 'module:users'])->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
        });

        Route::middleware(['permission:manage_departments', 'module:departments'])->group(function () {
            Route::resource('departments', DepartmentController::class)->except(['show']);
        });

        Route::middleware(['permission:manage_settings', 'module:settings'])->group(function () {
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
        });

        Route::middleware(['permission:manage_roles', 'module:roles'])->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::get('/role-assignments', [UserRoleController::class, 'index'])->name('role-assignments.index');
            Route::patch('/role-assignments/{user}', [UserRoleController::class, 'update'])->name('role-assignments.update');
        });
    });

    Route::middleware('role:admission')->prefix('admission')->name('admission.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
    });

    Route::middleware('role:department')->prefix('department')->name('department.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
    });

    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::middleware('module:documents')->group(function () {
            Route::get('/documents', [StudentDocumentController::class, 'index'])->name('documents.index');
            Route::post('/documents', [StudentDocumentController::class, 'store'])->name('documents.store');
            Route::delete('/documents/{document}', [StudentDocumentController::class, 'destroy'])->name('documents.destroy');
            Route::get('/documents/{document}/download', [StudentDocumentController::class, 'download'])->name('documents.download');
        });
    });

    Route::prefix('support-updates')->name('support-updates.')->middleware('module:support_updates')->group(function () {
        Route::get('/', [SupportUpdatesController::class, 'index'])->name('index');
        Route::post('/check', [SupportUpdatesController::class, 'check'])->name('check');
        Route::post('/check-json', [SupportUpdatesController::class, 'checkJson'])->name('check-json');
        Route::post('/acknowledge', [SupportUpdatesController::class, 'acknowledge'])->name('acknowledge');
        Route::post('/sync-latest', [SupportUpdatesController::class, 'syncLatest'])->name('sync-latest');
        Route::post('/requests', [SupportUpdatesController::class, 'storeRequest'])->name('requests.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [ProfileController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
