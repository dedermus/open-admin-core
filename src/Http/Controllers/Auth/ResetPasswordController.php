<?php

namespace OpenAdminCore\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use OpenAdminCore\Admin\Http\Controllers\Controller;
use OpenAdminCore\Admin\Http\Requests\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    /**
     * Показать форму сброса пароля.
     *
     * @param Request $request
     * @param string|null $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('admin::auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Сбросить пароль.
     *
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(ResetPasswordRequest $request)
    {
        // Используем брокер 'admin'
        $response = Password::broker('admin')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();

                // Логируем успешный сброс
                Log::channel('password_reset')->info('Password reset successful', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')
                ->with('status', __($response));
        }

        throw ValidationException::withMessages([
            'email' => [__($response)],
        ]);
    }
}
