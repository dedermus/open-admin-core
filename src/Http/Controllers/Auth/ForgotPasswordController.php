<?php

namespace OpenAdminCore\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use OpenAdminCore\Admin\Http\Controllers\Controller;
use OpenAdminCore\Admin\Http\Requests\ForgotPasswordRequest;

class ForgotPasswordController extends Controller
{
    /**
     * Показать форму запроса сброса пароля.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('admin::auth.forgot-password');
    }

    /**
     * Отправить ссылку на сброс пароля.
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $credential = $request->input('credential');

        // Ищем пользователя по email или username
        $user = $this->getUserByCredential($credential);

        if (!$user) {
            $this->logAttempt($credential, 'user_not_found');
            return $this->sendGenericResponse();
        }

        // Проверяем наличие email у пользователя
        if (!$user->email) {
            $this->logAttempt($credential, 'no_email', $user->id);
            return $this->sendGenericResponse();
        }

        // Используем брокер 'admin'
        $response = Password::broker('admin')->sendResetLink(
            ['email' => $user->email]
        );

        if ($response === Password::RESET_LINK_SENT) {
            $this->logAttempt($credential, 'success', $user->id);
            return back()->with('status', __($response));
        }

        $this->logAttempt($credential, 'failed', $user->id);

        return $this->sendGenericResponse();
    }

    /**
     * Найти пользователя по email или username.
     *
     * @param string $credential
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getUserByCredential(string $credential)
    {
        $userModel = config('admin.database.users_model');

        return $userModel::where('email', $credential)
            ->orWhere('username', $credential)
            ->first();
    }

    /**
     * Отправить общий ответ (без указания существования email).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendGenericResponse()
    {
        return back()->with('status', __('admin.password_reset.sent_if_exists'));
    }

    /**
     * Логирование попыток сброса.
     *
     * @param string $credential
     * @param string $status
     * @param int|null $userId
     * @return void
     */
    protected function logAttempt(string $credential, string $status, ?int $userId = null): void
    {
        Log::channel('password_reset')->info('Password reset attempt', [
            'credential' => $credential,
            'status' => $status,
            'user_id' => $userId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
