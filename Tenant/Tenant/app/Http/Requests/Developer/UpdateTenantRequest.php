<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'developer';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->route('tenant')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'school_type' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'plan_type' => ['required', Rule::in(['basic', 'pro'])],
            'plan_started_at' => ['nullable', 'date'],
            'plan_due_at' => ['nullable', 'date', 'after_or_equal:plan_started_at'],
            'plan_expiration_email' => ['required', 'email', 'max:255'],
            'signup_admin_name' => ['required', 'string', 'max:255'],
            'tenant_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?=.{1,255}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/',
                Rule::unique('schools', 'tenant_domain')->ignore($tenantId),
            ],
            'tenant_database' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9_]+$/',
                Rule::unique('schools', 'tenant_database')->ignore($tenantId),
            ],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }
}
