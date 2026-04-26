<?php

use App\Http\Controllers\Api\StudentStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'tenant.active', 'permission:manage_status_updates', 'module:status_monitoring', 'role:admin,tenant_admin,admission,faculty', 'status.role'])
    ->post('/students/{student}/status', [StudentStatusController::class, 'setStatus'])
    ->name('api.students.set-status');
