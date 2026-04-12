<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Developer\PlanController;
use App\Http\Controllers\Developer\SupportTicketController;
use App\Http\Controllers\Developer\TenantController;
use App\Http\Controllers\Developer\TenantRequestController;
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
    Route::get('/tenant-requests', [TenantRequestController::class, 'index'])->name('tenant-requests.index');
    Route::get('/tenant-requests/{tenantRequest}', [TenantRequestController::class, 'show'])->name('tenant-requests.show');
    Route::patch('/tenant-requests/{tenantRequest}/approve', [TenantRequestController::class, 'approve'])->name('tenant-requests.approve');
    Route::patch('/tenant-requests/{tenantRequest}/reject', [TenantRequestController::class, 'reject'])->name('tenant-requests.reject');
    Route::get('/tenants/plan-management', [TenantController::class, 'planManagement'])->name('tenants.plan-management');
    Route::get('/tenants/monitoring', [TenantController::class, 'monitoring'])->name('tenants.monitoring');
    Route::patch('/tenants/{tenant}/extend-plan', [TenantController::class, 'extendPlan'])->name('tenants.extend-plan');
    Route::post('/tenants/{tenant}/send-reminder', [TenantController::class, 'sendReminder'])->name('tenants.send-reminder');
    Route::post('/tenants/monitoring/sync-expired', [TenantController::class, 'syncExpiredTenants'])->name('tenants.sync-expired');
    Route::post('/tenants/monitoring/sync-usage', [TenantController::class, 'syncUsageMetrics'])->name('tenants.sync-usage');
    Route::patch('/tenants/{tenant}/approve', [TenantController::class, 'approve'])->name('tenants.approve');
    Route::patch('/tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
    Route::patch('/tenants/{tenant}/usage', [TenantController::class, 'updateUsage'])->name('tenants.update-usage');
    Route::patch('/tenants/{tenant}/subscription', [TenantController::class, 'updateSubscription'])->name('tenants.update-subscription');
    Route::post('/plans/{plan}/assign', [PlanController::class, 'assign'])->name('plans.assign');
    Route::resource('plans', PlanController::class)->except(['show']);
    Route::resource('support-tickets', SupportTicketController::class)->except(['show']);
    Route::resource('tenants', TenantController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
