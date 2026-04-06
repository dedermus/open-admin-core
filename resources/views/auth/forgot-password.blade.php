@extends('admin::layouts.auth')
@section('title', __('admin.password_reset.title'))
@section('content')
    <h4 class="text-center">
        <i class="icon-envelope me-2"></i>
        {{ __('admin.password_reset.title') }}
    </h4>

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.password.email') }}"
          novalidate
          id="password-reset-form"
          class="needs-validation">
        @csrf

        <div class="mb-3">
            <label for="credential" class="form-label">{{ __('admin.username_or_email') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-user"></i></span>
                <input type="text"
                       class="form-control @error('credential') is-invalid @enderror"
                       id="credential"
                       name="credential"
                       value="{{ old('credential') }}"
                       autofocus
                       required>
            </div>
            @error('credential')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit"
                class="btn btn-primary w-100"
                id="submitButton"
                data-original-html="{{ __('admin.send_password_reset_link') }}"
                data-loading-text="{{ __('admin.sending') ?? 'Отправка...' }}"
                data-timeout-message="{{ __('admin.timeout_message') ?? 'Превышено время ожидания ответа от сервера. Пожалуйста, проверьте соединение и попробуйте снова.' }}">
            {{ __('admin.send_password_reset_link') }}
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
    <script src="{{ Admin::asset("js/forgot-password.js") }}"></script>
@endpush
