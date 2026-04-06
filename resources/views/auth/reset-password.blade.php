@extends('admin::layouts.auth')
@section('title', __('admin.reset_password'))
@section('content')
    <h4 class="text-center">
        <i class="icon-lock me-2"></i>
        {{ __('admin.reset_password') }}
    </h4>

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.password.update') }}"
          novalidate
          id="password-reset-form"
          class="needs-validation">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('admin.email') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-envelope"></i></span>
                <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ $email ?? old('email') }}"
                       required
                       autofocus>
            </div>
            @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('admin.new_password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-key"></i></span>
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       required>
                <span class="input-group-text password-toggle"
                      role="button"
                      tabindex="0"
                      aria-label="{{ __('admin.toggle_password_visibility') ?? 'Toggle password visibility' }}"
                      aria-pressed="false">
                    <i class="icon-eye-slash" id="passwordToggleIcon"></i>
                </span>
            </div>
            @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('admin.confirm_password') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-check"></i></span>
                <input type="password"
                       class="form-control"
                       id="password_confirmation"
                       name="password_confirmation"
                       required>
                <span class="input-group-text password-toggle-confirm"
                      role="button"
                      tabindex="0"
                      aria-label="{{ __('admin.toggle_password_visibility') ?? 'Toggle password visibility' }}"
                      aria-pressed="false">
                    <i class="icon-eye-slash" id="confirmPasswordToggleIcon"></i>
                </span>
            </div>
        </div>

        <button type="submit"
                class="btn btn-primary w-100"
                id="submitButton"
                data-original-html="{{ __('admin.reset_password') }}"
                data-loading-text="{{ __('admin.sending') ?? 'Сохранение...' }}"
                data-timeout-message="{{ __('admin.timeout_message') ?? 'Превышено время ожидания ответа от сервера. Пожалуйста, проверьте соединение и попробуйте снова.' }}">
            <i class="icon-check me-2"></i>
            {{ __('admin.reset_password') }}
        </button>

        <div class="text-center mt-3">
            <a href="{{ url(config('admin.route.prefix', 'admin').'/auth/login') }}">
                <i class="icon-arrow-left me-1"></i>
                {{ __('admin.back_to_login') }}
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ Admin::asset("js/reset-password.js") }}"></script>
@endpush
