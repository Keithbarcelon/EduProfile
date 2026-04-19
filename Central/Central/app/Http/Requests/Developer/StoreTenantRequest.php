<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'developer';
    }

    protected function prepareForValidation(): void
    {
        $domain = $this->input('tenant_domain');
        if ($domain && !str_ends_with($domain, '.localhost')) {
            $this->merge([
                'tenant_domain' => $domain . '.localhost',
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'school_type' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'plan_type' => ['required', Rule::in(['basic', 'standard', 'premium'])],
            'plan_started_at' => ['nullable', 'date'],
            'plan_due_at' => ['nullable', 'date', 'after_or_equal:plan_started_at'],
            'billing_cycle' => ['nullable', Rule::in(['monthly', 'annual'])],
            'free_trial_days' => ['nullable', Rule::in([0, 7, 14])],
            'plan_expiration_email' => ['nullable', 'email', 'max:255'],
            'signup_admin_name' => ['required', 'string', 'max:255'],
            'tenant_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?=.{1,255}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/',
                Rule::unique('schools', 'tenant_domain'),
                Rule::unique('schools', 'requested_tenant_domain'),
            ],
            'tenant_database' => ['prohibited'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }
}
