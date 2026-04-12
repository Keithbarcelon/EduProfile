<?php

namespace App\Http\Requests\Developer;

use App\Models\TenantRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveTenantRequestActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'developer';
    }

    protected function prepareForValidation(): void
    {
        $domain = $this->input('tenant_domain');

        if (! is_string($domain)) {
            return;
        }

        $domain = strtolower(trim($domain));

        if ($domain === '') {
            $this->merge(['tenant_domain' => null]);

            return;
        }

        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = explode('/', $domain)[0] ?? $domain;
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;

        if (! str_contains($domain, '.')) {
            $domain .= '.localhost';
        }

        $this->merge(['tenant_domain' => $domain]);
    }

    public function rules(): array
    {
        $tenantRequestId = $this->route('tenantRequest')?->id;

        return [
            'tenant_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?=.{1,255}$)([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/',
                Rule::unique('schools', 'tenant_domain'),
                Rule::unique('schools', 'requested_tenant_domain'),
                Rule::unique('tenant_requests', 'requested_tenant_domain')
                    ->ignore($tenantRequestId)
                    ->where(fn ($query) => $query->where('status', TenantRequest::STATUS_PENDING)),
            ],
        ];
    }
}
