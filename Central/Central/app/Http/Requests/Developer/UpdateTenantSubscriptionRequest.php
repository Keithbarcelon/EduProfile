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
            'plan_type' => ['required', Rule::in(['basic', 'standard', 'premium'])],
            'plan_started_at' => ['required', 'date'],
            'plan_due_at' => ['required', 'date', 'after_or_equal:plan_started_at'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'free_trial_days' => ['nullable', Rule::in([0, 7, 14])],
            'plan_expiration_email' => ['required', 'email', 'max:255'],
        ];
    }
}
