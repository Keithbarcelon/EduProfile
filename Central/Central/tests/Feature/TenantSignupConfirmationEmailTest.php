<?php

use App\Notifications\TenantRequestReceivedNotification;
use Illuminate\Support\Facades\Notification;

it('sends pending notification after tenant request submission', function () {
    Notification::fake();

    $payload = [
        'tenant_name' => 'Sample Academy',
        'address' => 'Sample City',
        'plan_type' => 'basic',
        'signup_admin_name' => 'Sample Admin',
        'admin_email' => 'admin@sample.test',
        'admin_password' => 'password123',
        'admin_password_confirmation' => 'password123',
        'plan_expiration_email' => 'admin@sample.test',
        'tenant_domain' => 'sample-academy',
    ];

    $response = $this->post(route('tenant-signup.store'), $payload);

    $response->assertRedirect(route('tenant-signup.create'));
    $response->assertSessionHas('success');
    $response->assertSessionHas('tenant_requested_domain', 'sample-academy.localhost');

    $this->assertDatabaseHas('tenant_requests', [
        'tenant_name' => 'Sample Academy',
        'admin_email' => 'admin@sample.test',
        'status' => 'pending',
        'requested_tenant_domain' => 'sample-academy.localhost',
    ]);

    $this->assertDatabaseMissing('schools', [
        'name' => 'Sample Academy',
    ]);

    Notification::assertSentOnDemand(
        TenantRequestReceivedNotification::class,
        function (TenantRequestReceivedNotification $notification, array $channels, object $notifiable) {
            return in_array('mail', $channels, true)
                && (($notifiable->routes['mail'] ?? null) === 'admin@sample.test');
        }
    );
});