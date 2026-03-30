<?php

namespace OpenAdminCore\Admin\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Support\ServiceProvider;
use OpenAdminCore\Admin\Auth\Database\Administrator;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerAdminAuthProvider();
    }

    /**
     * Register the admin auth provider.
     */
    protected function registerAdminAuthProvider(): void
    {
        $this->app->make(AuthManager::class)->provider('admin', function ($app, array $config) {
            return new AdminAuthProvider($app['hash'], $config['model']);
        });

        // Регистрируем провайдер для брокера паролей
        $this->app->make(AuthManager::class)->provider('admin-users', function ($app, array $config) {
            return new AdminUserProvider($app['hash'], $config['model']);
        });
    }
}
