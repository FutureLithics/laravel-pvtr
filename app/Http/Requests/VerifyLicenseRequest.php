<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'license_number' => ['required', 'string', 'max:255'],
            'license_prefix' => ['nullable', 'required_without_all:email,entity_name', 'string', 'max:255'],
            'email' => ['nullable', 'required_without_all:license_prefix,entity_name', 'email', 'max:255'],
            'entity_name' => ['nullable', 'required_without_all:license_prefix,email', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $corroborating = 'Enter a license prefix, email, or entity name to verify this license.';

        return [
            'license_prefix.required_without_all' => $corroborating,
            'email.required_without_all' => $corroborating,
            'entity_name.required_without_all' => $corroborating,
        ];
    }
}
