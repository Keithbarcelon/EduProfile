<?php

use App\Models\TenantRequest;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('tenant signup request can be submitted from register route', function () {
    $response = $this->post('/register', [
        'tenant_name' => 'Test University',
        'address' => 'Main Avenue',
        'plan_type' => 'basic',
        'signup_admin_name' => 'Registrar User',
        'admin_email' => 'registrar@example.com',
        'admin_password' => 'StrongPass!1234',
        'admin_password_confirmation' => 'StrongPass!1234',
        'plan_expiration_email' => 'billing@example.com',
        'tenant_domain' => 'test-university.localhost',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('tenant-signup.create', absolute: false));

    $this->assertDatabaseHas('tenant_requests', [
        'tenant_name' => 'Test University',
        'admin_email' => 'registrar@example.com',
        'status' => TenantRequest::STATUS_PENDING,
    ]);
});
