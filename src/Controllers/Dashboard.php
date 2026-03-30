<?php

namespace OpenAdminCore\Admin\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use OpenAdminCore\Admin\Admin;

class Dashboard
{
    /**
     * @return Factory|View
     */
    public static function title()
    {
        return view('admin::dashboard.title');
    }

    /**
     * @return Factory|View
     */
    public static function environment()
    {
        $envs = [
            ['name' => 'PHP version',       'value' => 'PHP/'.PHP_VERSION],
            ['name' => 'Laravel version',   'value' => app()->version()],
            ['name' => 'CGI',               'value' => php_sapi_name()],
            ['name' => 'Uname',             'value' => php_uname()],
            ['name' => 'Server',            'value' => Arr::get($_SERVER, 'SERVER_SOFTWARE')],

            ['name' => 'Cache driver',      'value' => config('cache.default')],
            ['name' => 'Session driver',    'value' => config('session.driver')],
            ['name' => 'Queue driver',      'value' => config('queue.default')],

            ['name' => 'Timezone',          'value' => config('app.timezone')],
            ['name' => 'Locale',            'value' => config('app.locale')],
            ['name' => 'Env',               'value' => config('app.env')],
            ['name' => 'URL',               'value' => config('app.url')],
        ];

        return view('admin::dashboard.environment', compact('envs'));
    }

    /**
     * @return Factory|View
     */
    public static function extensions()
    {
        $extensions = [
            'helpers' => [
                'name' => 'open-admin-ext/helpers',
                'link' => 'https://github.com/dedermus/helpers',
                'icon' => 'cogs',
            ],
            'log-viewer' => [
                'name' => 'open-admin-ext/log-viewer',
                'link' => 'https://github.com/dedermus/log-viewer',
                'icon' => 'database',
            ],
            'backup' => [
                'name' => 'open-admin-ext/backup',
                'link' => 'https://github.com/dedermus/backup',
                'icon' => 'copy',
            ],
            'config' => [
                'name' => 'open-admin-ext/config',
                'link' => 'https://github.com/dedermus/config',
                'icon' => 'toggle-on',
            ],
            'api-tester' => [
                'name' => 'open-admin-ext/api-tester',
                'link' => 'https://github.com/dedermus/api-tester',
                'icon' => 'sliders-h',
            ],
            'media-manager' => [
                'name' => 'open-admin-ext/media-manager',
                'link' => 'https://github.com/dedermus/media-manager',
                'icon' => 'file',
            ],
            'scheduling' => [
                'name' => 'open-admin-ext/scheduling',
                'link' => 'https://github.com/dedermus/scheduling',
                'icon' => 'clock',
            ],
            'reporter' => [
                'name' => 'open-admin-ext/reporter',
                'link' => 'https://github.com/dedermus/reporter',
                'icon' => 'bug',
            ],
            'redis-manager' => [
                'name' => 'open-admin-ext/redis-manager',
                'link' => 'https://github.com/dedermus/redis-manager',
                'icon' => 'flask',
            ],
            'grid-sortable' => [
                'name' => 'open-admin-ext/grid-sortable',
                'link' => 'https://github.com/dedermus/grid-sortable',
                'icon' => 'arrows-alt-v',
            ],
        ];

        foreach ($extensions as &$extension) {
            $name = explode('/', $extension['name']);
            $extension['installed'] = array_key_exists(end($name), Admin::$extensions);
        }

        return view('admin::dashboard.extensions', compact('extensions'));
    }

    /**
     * @return Factory|View
     */
    public static function dependencies()
    {
        $json = file_get_contents(base_path('composer.json'));

        $dependencies = json_decode($json, true)['require'];

        return Admin::component('admin::dashboard.dependencies', compact('dependencies'));
    }
}
