<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantSubscriptionRequest extends FormRequest
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
        return [
            'plan_type' => ['required', Rule::in(['basic', 'pro'])],
            'plan_started_at' => ['required', 'date'],
            'plan_due_at' => ['required', 'date', 'after_or_equal:plan_started_at'],
            'plan_expiration_email' => ['required', 'email', 'max:255'],
        ];
    }
}
