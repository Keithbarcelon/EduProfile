<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\TenantRequest;

class StoreTenantSignupRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $domain = $this->input('tenant_domain');

        if (! is_string($domain)) {
            return;
        }

        $domain = strtolower(trim($domain));

        if ($domain === '') {
            return;
        }

        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = explode('/', $domain)[0] ?? $domain;
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;

        // Allow short tenant keys (e.g., "myschool") by appending base domain.
        if (! str_contains($domain, '.')) {
            $baseDomain = (string) env('TENANT_BASE_DOMAIN', 'localhost');
            $baseDomain = strtolower(trim($baseDomain));
            $baseDomain = preg_replace('/^www\./', '', $baseDomain) ?? $baseDomain;

            if ($baseDomain !== '') {
                $domain = "{$domain}.{$baseDomain}";
            }
        }

        $this->merge([
            'tenant_domain' => $domain,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tenant_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'plan_type' => ['required', Rule::in(['basic', 'standard', 'premium'])],
            'signup_admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                Rule::unique('tenant_requests', 'admin_email')
                    ->where(fn ($query) => $query->where('status', TenantRequest::STATUS_PENDING)),
            ],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan_expiration_email' => ['nullable', 'email', 'max:255'],
            'tenant_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?=.{1,255}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/',
                Rule::unique('schools', 'tenant_domain'),
                Rule::unique('schools', 'requested_tenant_domain'),
                Rule::unique('tenant_requests', 'requested_tenant_domain')
                    ->where(fn ($query) => $query->where('status', TenantRequest::STATUS_PENDING)),
            ],
        ];
    }
}
