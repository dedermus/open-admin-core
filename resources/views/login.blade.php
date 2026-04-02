@extends('admin::layouts.auth')
@section('title', __('admin.authorization'))
@section('content')
    <h4 class="text-center">
        <i class="icon-shield-alt me-2"></i>
        {{ __('admin.authorization') }}
    </h4>
    @if($errors->has('attempts'))
        <div class="alert alert-danger m-0 text-center">{!! $errors->first('attempts') !!}</div>
    @else
        @if($errors->has('attempts_auth'))
            <div class="alert alert-danger m-0 text-center">{!! $errors->first('attempts_auth') !!}</div>
        @endif
        <form action="{{ admin_url('auth/login') }}" method="post" novalidate>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="mb-3">

                <label for="username" class="form-label">{{ __('admin.username_or_email') }}</label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="icon-user"></i></span>
                    <input type="text" class="form-control" autocomplete="off" placeholder="{{ __('admin.username') }}"
                           name="username" id="username" value="{{ old('username') }}" required>
                </div>
                @error('username')
                <div id="username-error" class="invalid-feedback d-block mt-2" role="alert">
                    <i class="icon-exclamation-circle me-1"></i>
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">{{ __('admin.password') }}</label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="icon-lock text-muted"></i></span>
                    <input type="password"
                           class="form-control"
                           placeholder="{{ __('admin.password') }}"
                           name="password"
                           id="password"
                           required
                           aria-required="true"
                           aria-describedby="password-error password-strength">
                    <span class="input-group-text password-toggle"
                          role="button"
                          tabindex="0"
                          aria-label="{{ __('admin.toggle_password_visibility') ?? 'Toggle password visibility' }}"
                          aria-pressed="false">
                                    <i class="icon-eye-slash" id="passwordToggleIcon"></i>
                                </span>
                </div>
                @error('password')
                <div id="password-error" class="invalid-feedback d-block mt-2" role="alert">
                    <i class="icon-exclamation-circle me-1"></i>
                    {{ $message }}
                </div>
                @enderror
            </div>
            @if(config('admin.auth.remember') || config('admin.auth.password_reset.enabled', true))
                <div class="mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        @if(config('admin.auth.remember'))
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="remember"
                                       id="remember"
                                       value="1"
                                    {{ session('form_data.remember', old('remember')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    <i class="icon-check me-1"></i>
                                    {{ __('admin.remember_me') }}
                                </label>
                            </div>
                        @endif

                        @if(config('admin.auth.password_reset.enabled', true))
                            <div>
                                <a href="{{ route('admin.password.request') }}" class="text-decoration-none small">
                                    <i class="icon-question-circle me-1"></i>
                                    {{ __('admin.forgot_password') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <div class="mb-3">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="loginButton"><i class="icon-sign-in me-2"></i>{{ __('admin.login') }}</button>
                </div>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            // Password visibility toggle
            const passwordToggle = document.querySelector('.password-toggle');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');

            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', togglePasswordVisibility);
                passwordToggle.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        togglePasswordVisibility();
                    }
                });

                function togglePasswordVisibility() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'text') {
                        toggleIcon.classList.remove('icon-eye-slash');
                        toggleIcon.classList.add('icon-eye');
                        passwordToggle.setAttribute('aria-pressed', 'true');
                        passwordToggle.setAttribute('aria-label', 'Hide password');
                    } else {
                        toggleIcon.classList.remove('icon-eye');
                        toggleIcon.classList.add('icon-eye-slash');
                        passwordToggle.setAttribute('aria-pressed', 'false');
                        passwordToggle.setAttribute('aria-label', 'Show password');
                    }

                    passwordInput.focus();
                }
            }
            // Form submission with loading state
            const form = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            // Prevent double submission
            let submitted = false;
            if (form) {
                form.addEventListener('submit', function() {
                    if (submitted) {
                        return false;
                    }
                    submitted = true;
                    return true;
                });
            }
        })();
    </script>
@endpush
