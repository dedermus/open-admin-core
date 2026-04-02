<?php

namespace OpenAdminCore\Admin\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use OpenAdminCore\Admin\Facades\Admin;
use OpenAdminCore\Admin\Form;
use OpenAdminCore\Admin\Layout\Content;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected string $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return Factory|View|Application|RedirectResponse|Redirector
     */
    public function getLogin(): View|Application|Factory|Redirector|RedirectResponse
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws ValidationException
     */
    public function postLogin(Request $request): Response|RedirectResponse
    {
        $rate_limit_key = 'login-tries-'.Admin::guardName();

        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember    = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            RateLimiter::clear($rate_limit_key);

            return $this->sendLoginResponse($request);
        }

        if (config('admin.auth.throttle_logins')) {
            $throttle_timeout = config('admin.auth.throttle_timeout', 600);
            RateLimiter::hit($rate_limit_key, $throttle_timeout);
        }

        return back()->withInput()->withErrors([
            'attempts_auth' => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            $this->username() => 'required|string', // или 'required|string'
            'password' => 'required|string|min:5', // добавили min:5
        ];

        $messages = [
            'username.required' => trans('auth.validation.username_required'),
            'username.email'    => trans('auth.validation.username_email'),
            'password.required' => trans('auth.validation.password_required'),
            'password.min'      => trans('auth.validation.password_min', ['min' => 5]),
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * User logout.
     *
     * @param Request $request
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function getLogout(Request $request): Application|Redirector|RedirectResponse
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content): Content
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        return $content
            ->title(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm(): Form
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->email('email', __('admin.email'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * @return array|Translator|Application|string
     */
    protected function getFailedLoginMessage(): Application|array|string|Translator
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return Repository|Application|mixed|null
     */
    protected function redirectPath(): mixed
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    protected function sendLoginResponse(Request $request): RedirectResponse
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username(): string
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return Guard|StatefulGuard
     */
    protected function guard(): Guard|StatefulGuard
    {
        return Admin::guard();
    }
}
