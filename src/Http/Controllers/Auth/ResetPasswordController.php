<?php

namespace OpenAdminCore\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use OpenAdminCore\Admin\Http\Controllers\Controller;
use OpenAdminCore\Admin\Http\Requests\ResetPasswordRequest;
use OpenAdminCore\Admin\Notifications\PasswordResetSuccess;

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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(ResetPasswordRequest $request)
    {
        // Используем брокер 'admin'
        $response = Password::broker('admin')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->password = Hash::make($password);

                // Принудительный выход из всех устройств
                $user->remember_token = null;
                $user->save();



                // Отправляем уведомление на email
                $user->notify(new PasswordResetSuccess(
                    $request->ip(),
                    $request->userAgent()
                ));

                // Логируем успешный сброс
                Log::channel('password_reset')->info('Password reset successful', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        );

        // Обработка AJAX запроса
        if ($request->ajax() || $request->wantsJson()) {
            if ($response === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => __($response),
                    'redirect' => route('admin.login', [], false)
                ], 200);
            }

            // Обработка throttle (слишком много попыток)
            if ($response === Password::RESET_THROTTLED) {
                return response()->json([
                    'success' => false,
                    'message' => __($response),
                    'errors' => ['throttle' => [__($response)]]
                ], 429);
            }

            // Остальные ошибки
            return response()->json([
                'success' => false,
                'message' => __($response),
                'errors' => ['email' => [__($response)]]
            ], 422);
        }

        // Обычный (не-AJAX) ответ
        if ($response === Password::PASSWORD_RESET) {
            return redirect()->to('/admin/auth/login')
                ->with('status', __($response));
        }

        throw ValidationException::withMessages([
            'email' => [__($response)],
        ]);
    }
}
