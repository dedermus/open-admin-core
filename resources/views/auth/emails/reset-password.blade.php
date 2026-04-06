@component('mail::message')
    {{ __('admin.password_reset.subject') }}

    {{ __('admin.password_reset.intro') }}

    @component('mail::button', ['url' => $resetUrl])
        {{ __('admin.password_reset.action') }}
    @endcomponent

    {{ __('admin.password_reset.expire', ['minutes' => $expireMinutes]) }}

    {{ __('admin.password_reset.outro') }}

    {{ __('admin.thanks') }},<br>
    {{ config('app.name') }}
@endcomponent
