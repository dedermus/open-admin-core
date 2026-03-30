<?php

use Illuminate\Support\Facades\Route;

/**
 * Маршруты восстановления пароля.
 * Добавляются в существующую группу маршрутов админ-панели.
 */
Route::group([
    'prefix' => config('admin.route.prefix', 'admin'),
    'namespace' => 'OpenAdminCore\Admin\Http\Controllers\Auth',
    'middleware' => ['web'],
], function () {
    // Маршруты для гостей (не авторизованных пользователей)
    Route::group(['middleware' => ['guest']], function () {
        Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')
            ->name('admin.password.request');

        Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')
            ->middleware(['admin.throttle.password_reset'])
            ->name('admin.password.email');

        Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')
            ->name('admin.password.reset');

        Route::post('password/reset', 'ResetPasswordController@reset')
            ->name('admin.password.update');
    });
});
