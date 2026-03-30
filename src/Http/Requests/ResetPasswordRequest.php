<?php

namespace OpenAdminCore\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordRequest extends FormRequest
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
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token.required' => __('admin::admin.validation.token_required'),
            'email.required' => __('admin::admin.validation.email_required'),
            'email.email' => __('admin::admin.validation.email_invalid'),
            'password.required' => __('admin::admin.validation.password_required'),
            'password.confirmed' => __('admin::admin.validation.password_confirmed'),
        ];
    }
}
