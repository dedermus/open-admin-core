<?php

namespace OpenAdminCore\Admin\Traits;

use Illuminate\Support\Facades\Event;

trait HasHooks
{
    /**
     * Call a hook and collect all listener responses.
     *
     * @param string $event
     * @param array $payload
     * @return string
     */
    protected static function callHook($event, $payload = [])
    {
        $results = Event::dispatch($event, $payload);

        if (empty($results)) {
            return '';
        }

        // If only one result, return it directly
        if (count($results) === 1 && (is_string($results[0]) || is_numeric($results[0]))) {
            return (string) $results[0];
        }

        // Combine all results from multiple listeners
        return collect($results)->filter()->implode('');
    }

    /**
     * Login form hooks.
     */
    public static function loginFormStart()
    {
        return static::callHook('admin.login.form.start');
    }

    public static function loginFormFields()
    {
        return static::callHook('admin.login.form.fields');
    }

    public static function loginFormEnd()
    {
        return static::callHook('admin.login.form.end');
    }

    public static function loginFormScripts()
    {
        return static::callHook('admin.login.form.scripts');
    }

    /**
     * Navbar hooks.
     */
    public static function navbarLeft()
    {
        return static::callHook('admin.navbar.left');
    }

    public static function navbarRight()
    {
        return static::callHook('admin.navbar.right');
    }

    /**
     * Register an event listener.
     *
     * @param string $event
     * @param callable $callback
     */
    public static function listen($event, $callback)
    {
        Event::listen($event, $callback);
    }
}
