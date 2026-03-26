<?php

namespace Lartisan\FacebookDataDeletion\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FacebookDataDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'signed_request' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'signed_request.required' => 'Facebook signed_request is required.',
        ];
    }
}
