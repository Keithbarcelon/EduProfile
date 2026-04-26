<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\TrackBandwidthUsage;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureTenantIsActive;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Resolve the tenant before any other web middleware (sessions, auth, etc.) run
        $middleware->prependToGroup('web', ResolveTenant::class);
        $middleware->appendToGroup('web', TrackBandwidthUsage::class);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'tenant.active' => EnsureTenantIsActive::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'module' => \App\Http\Middleware\EnsureTenantModuleEnabled::class,
            'feature' => \App\Http\Middleware\EnsureTenantFeatureEnabled::class,
            'status.role' => \App\Http\Middleware\EnsureStudentStatusRoleRestrictions::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
