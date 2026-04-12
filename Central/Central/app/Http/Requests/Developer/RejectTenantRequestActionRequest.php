<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;

class RejectTenantRequestActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'developer';
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
