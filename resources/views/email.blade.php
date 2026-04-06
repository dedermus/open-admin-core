@extends('admin::layouts.app')

@section('content')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/admin') }}">{{ config('app.name', 'Admin') }}</a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Введите ваш email для восстановления пароля</p>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.password.email') }}">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Отправить ссылку</button>
                        </div>
                    </div>
                </form>
                <p class="mt-3 mb-1">
                    <a href="{{ route('admin.login') }}">Вернуться к входу</a>
                </p>
            </div>
        </div>
    </div>
@endsection
