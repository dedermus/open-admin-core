<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(config('admin.rtl', false)) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('admin.title') }} | @yield('title', __('admin.login'))</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(!is_null($favicon = Admin::favicon()))
        <title>{{ config('admin.title') }} | @yield('title', __('admin.login'))</title>
    @endif

    <link rel="stylesheet" href="{{ Admin::asset("open-admin/css/styles.css")}}">
    <script src="{{ Admin::asset("bootstrap5/bootstrap.bundle.min.js")}}"></script>
    @stack('styles')

</head>
<body class="bg-light" @if(config('admin.login_background_image')) style="background: url({{ config('admin.login_background_image') }}) no-repeat;background-size: cover;" @endif>
<div class="d-flex justify-content-center align-items-center h-100">
    <div class="container m-4" style="max-width: 450px;">
        <h1 class="text-center mb-3 h2">
            <a class="text-decoration-none text-dark" href="{{ admin_url('/') }}">
                {{ config('admin.name') }}
            </a>
        </h1>

        <div class="bg-body p-4 shadow-sm rounded-3">
            @yield('content')
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
