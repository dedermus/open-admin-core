<?php

namespace OpenAdminCore\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'credential' => 'required|string|max:320',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'credential.required' => __('admin::admin.validation.credential_required'),
            'credential.max' => __('admin::admin.validation.credential_max'),
        ];
    }
}
