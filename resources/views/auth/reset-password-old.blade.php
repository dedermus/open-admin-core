@extends('admin::layouts.auth')
@section('title', __('admin.reset_password'))
@section('content')

    <h4 class="text-center"><i class="icon-lock me-2"></i>{{ __('admin.reset_password') }}</h4>

    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('admin.email') }}</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email"
                   name="email"
                   value="{{ $email ?? old('email') }}"
                   required
                   autofocus>
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('admin.password') }}</label>
            <input type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   id="password"
                   name="password"
                   required>
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                {{ __('admin.password_reset.password_requirements') }}
            </div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('admin.confirm_password') }}</label>
            <input type="password"
                   class="form-control"
                   id="password_confirmation"
                   name="password_confirmation"
                   required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
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
