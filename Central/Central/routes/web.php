<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Developer\AppUpdateController;
use App\Http\Controllers\Developer\PlanController;
use App\Http\Controllers\Developer\SupportTicketController;
use App\Http\Controllers\Developer\TenantCustomizationController;
use App\Http\Controllers\Developer\TenantController;
use App\Http\Controllers\Developer\VersionManagementController;
use App\Http\Controllers\Developer\SupportRequestController;
use App\Http\Controllers\TenantSignupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Tenant Signup Routes (School Registration) ──────────────────────────────
Route::middleware(['guest'])->group(function () {
    Route::get('/tenant-signup', [TenantSignupController::class, 'create'])->name('tenant-signup.create');
    Route::post('/tenant-signup', [TenantSignupController::class, 'store'])->name('tenant-signup.store');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'developer', 'central.only'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('developer.tenants.index'))->name('dashboard');
    Route::get('/tenants/plan-management', [TenantController::class, 'planManagement'])->name('tenants.plan-management');
    Route::get('/tenants/monitoring', [TenantController::class, 'monitoring'])->name('tenants.monitoring');
    Route::patch('/tenants/{tenant}/extend-plan', [TenantController::class, 'extendPlan'])->name('tenants.extend-plan');
    Route::post('/tenants/{tenant}/send-reminder', [TenantController::class, 'sendReminder'])->name('tenants.send-reminder');
    Route::post('/tenants/monitoring/sync-expired', [TenantController::class, 'syncExpiredTenants'])->name('tenants.sync-expired');
    Route::post('/tenants/monitoring/sync-usage', [TenantController::class, 'syncUsageMetrics'])->name('tenants.sync-usage');
    Route::patch('/tenants/{tenant}/approve', [TenantController::class, 'approve'])->name('tenants.approve');
    Route::patch('/tenants/{tenant}/reject', [TenantController::class, 'reject'])->name('tenants.reject');
    Route::patch('/tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
    Route::patch('/tenants/{tenant}/usage', [TenantController::class, 'updateUsage'])->name('tenants.update-usage');
    Route::patch('/tenants/{tenant}/subscription', [TenantController::class, 'updateSubscription'])->name('tenants.update-subscription');
    Route::get('/tenants/{tenant}/customization', [TenantCustomizationController::class, 'edit'])->name('tenants.customization.edit');
    Route::patch('/tenants/{tenant}/customization', [TenantCustomizationController::class, 'update'])->name('tenants.customization.update');
    Route::post('/plans/{plan}/assign', [PlanController::class, 'assign'])->name('plans.assign');
    Route::post('/plans/{plan}/duplicate', [PlanController::class, 'duplicate'])->name('plans.duplicate');
    Route::patch('/plans/{plan}/active', [PlanController::class, 'setActive'])->name('plans.set-active');
    Route::resource('plans', PlanController::class)->except(['show']);
    Route::resource('support-tickets', SupportTicketController::class)->except(['show']);
    Route::prefix('support-requests')->name('support-requests.')->group(function () {
        Route::get('/', [SupportRequestController::class, 'index'])->name('index');
        Route::get('/{supportRequest}', [SupportRequestController::class, 'show'])->name('show');
        Route::patch('/{supportRequest}/status', [SupportRequestController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{supportRequest}', [SupportRequestController::class, 'destroy'])->name('destroy');
    });
    Route::resource('app-updates', AppUpdateController::class)->except(['show']);
    Route::get('/version-management', [VersionManagementController::class, 'index'])->name('version-management.index');
    Route::post('/version-management/versions', [VersionManagementController::class, 'storeVersion'])->name('version-management.versions.store');
    Route::patch('/version-management/versions/{appVersion}/activate', [VersionManagementController::class, 'activateVersion'])->name('version-management.versions.activate');
    Route::post('/version-management/sync-github', [VersionManagementController::class, 'syncGithubLatest'])->name('version-management.sync-github');
    Route::patch('/version-management/support-requests/{supportRequest}/status', [VersionManagementController::class, 'updateSupportRequestStatus'])->name('version-management.support-requests.status');
    Route::resource('tenants', TenantController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
