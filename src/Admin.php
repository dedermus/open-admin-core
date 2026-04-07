<?php

namespace OpenAdminCore\Admin;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use OpenAdminCore\Admin\Auth\Database\Menu;
use OpenAdminCore\Admin\Controllers\AuthController;
use OpenAdminCore\Admin\Layout\Content;
use OpenAdminCore\Admin\Traits\HasAssets;
use OpenAdminCore\Admin\Traits\HasHooks;
use OpenAdminCore\Admin\Widgets\Navbar;

/**
 * Class Admin.
 */
class Admin
{
    use HasAssets, HasHooks;

    /**
     * The Open-admin version.
     *
     * @var string
     */
    public const VERSION = '2.0.01';

    /**
     * @var Navbar
     */
    protected $navbar;

    /**
     * @var array
     */
    protected $menu = [];

    /**
     * @var string
     */
    public static $metaTitle;

    /**
     * @var string
     */
    public static $favicon;

    /**
     * @var array
     */
    public static $extensions = [];

    /**
     * @var []Closure
     */
    protected static $bootingCallbacks = [];

    /**
     * @var []Closure
     */
    protected static $bootedCallbacks = [];

    /**
     * Returns the long version of Open-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Open-admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \OpenAdminCore\Admin\Grid
     *
     * @deprecated since v1.6.1
     */
    public function grid($model, Closure $callable)
    {
        return new Grid($this->getModel($model), $callable);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \OpenAdminCore\Admin\Form
     *
     *  @deprecated since v1.6.1
     */
    public function form($model, Closure $callable)
    {
        return new Form($this->getModel($model), $callable);
    }

    /**
     * Build a tree.
     *
     * @param $model
     * @param Closure|null $callable
     *
     * @return \OpenAdminCore\Admin\Tree
     */
    public function tree($model, Closure $callable = null)
    {
        return new Tree($this->getModel($model), $callable);
    }

    /**
     * Build show page.
     *
     * @param $model
     * @param mixed $callable
     *
     * @return Show
     */
    public function show($model, $callable = null)
    {
        return new Show($this->getModel($model), $callable);
    }

    /**
     * @param Closure $callable
     *
     * @return \OpenAdminCore\Admin\Layout\Content
     */
    public function content(Closure $callable = null)
    {
        return new Content($callable);
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public function menu()
    {
        if (!empty($this->menu)) {
            return $this->menu;
        }

        $menuClass = config('admin.database.menu_model');

        /** @var Menu $menuModel */
        $menuModel = new $menuClass();

        return $this->menu = $menuModel->toTree();
    }

    /**
     * @param array $menu
     *
     * @return array
     */
    public function menuLinks($menu = [])
    {
        if (empty($menu)) {
            $menu = $this->menu();
        }

        $links = [];

        foreach ($menu as $item) {
            if (!empty($item['children'])) {
                $links = array_merge($links, $this->menuLinks($item['children']));
            } else {
                $links[] = Arr::only($item, ['title', 'uri', 'icon']);
            }
        }

        return $links;
    }

    /**
     * Set admin title.
     *
     * @param string $title
     *
     * @return void
     */
    public static function setTitle($title)
    {
        self::$metaTitle = $title;
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('admin.title');
    }

    /**
     * @param null|string $favicon
     *
     * @return string|void
     */
    public function favicon($favicon = null)
    {
        if (is_null($favicon)) {
            return static::$favicon;
        }

        static::$favicon = $favicon;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return static::guard()->user();
    }

    /**
     * Get the guard name.
     *
     * @return string
     */
    public function guardName()
    {
        return config('admin.auth.guard') ?: 'admin';
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard()
    {
        return Auth::guard(static::guardName());
    }

    /**
     * Set navbar.
     *
     * @param Closure|null $builder
     *
     * @return Navbar
     */
    public function navbar(Closure $builder = null)
    {
        if (is_null($builder)) {
            return $this->getNavbar();
        }

        call_user_func($builder, $this->getNavbar());
    }

    /**
     * Get navbar object.
     *
     * @return \OpenAdminCore\Admin\Widgets\Navbar
     */
    public function getNavbar()
    {
        if (is_null($this->navbar)) {
            $this->navbar = new Navbar();
        }

        return $this->navbar;
    }

    /**
     * Register the open-admin builtin routes.
     *
     * @return void
     *
     * @deprecated Use Admin::routes() instead();
     */
    public function registerAuthRoutes()
    {
        $this->routes();
    }

    /**
     * Register the open-admin builtin routes.
     *
     * @return void
     */
    public function routes()
    {
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {
            /* @var \Illuminate\Support\Facades\Route $router */
            $router->namespace('\OpenAdminCore\Admin\Controllers')->group(function ($router) {
                /* @var \Illuminate\Routing\Router $router */
                $router->resource('auth/users', 'UserController')->names('admin.auth.users');
                $router->resource('auth/roles', 'RoleController')->names('admin.auth.roles');
                $router->resource('auth/permissions', 'PermissionController')->names('admin.auth.permissions');
                $router->resource('auth/menu', 'MenuController', ['except' => ['create']])->names('admin.auth.menu');
                $router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']])->names('admin.auth.logs');

                $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
                $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
                $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('admin.handle-selectable');
                $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('admin.handle-renderable');
            });

            $authController = config('admin.auth.controller', AuthController::class);

            /* @var \Illuminate\Routing\Router $router */
            $router->get('auth/login', $authController.'@getLogin')->name('admin.login');
            $router->post('auth/login', $authController.'@postLogin');
            $router->get('auth/logout', $authController.'@getLogout')->name('admin.logout');
            $router->get('auth/setting', $authController.'@getSetting')->name('admin.setting');
            $router->put('auth/setting', $authController.'@putSetting');
        });
    }

    /**
     * Extend a extension.
     *
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public static function extend($name, $class)
    {
        static::$extensions[$name] = $class;
    }

    /**
     * @param callable $callback
     */
    public static function booting(callable $callback)
    {
        static::$bootingCallbacks[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public static function booted(callable $callback)
    {
        static::$bootedCallbacks[] = $callback;
    }

    /**
     * Bootstrap the admin application.
     */
    public function bootstrap()
    {
        $this->fireBootingCallbacks();

        require config('admin.bootstrap', admin_path('bootstrap.php'));

        $this->addAdminAssets();

        $this->fireBootedCallbacks();
    }

    /**
     * Add JS & CSS assets to pages.
     */
    protected function addAdminAssets()
    {
        $assets = Form::collectFieldAssets();

        self::css($assets['css']);
        self::js($assets['js']);
    }

    /**
     * Call the booting callbacks for the admin application.
     */
    protected function fireBootingCallbacks()
    {
        foreach (static::$bootingCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /**
     * Call the booted callbacks for the admin application.
     */
    protected function fireBootedCallbacks()
    {
        foreach (static::$bootedCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    public static function asset($asset)
    {
        return url('/vendor/open-admin/'.$asset);
    }

    public static function js_trans()
    {
        $lang_array = json_encode(__('admin'));

        return '<script>var admin_lang_arr = '.$lang_array.'</script>';
    }

    /**
     * Хук: начало формы авторизации
     * Используется в resources/views/auth/login.blade.php
     */
    public static function loginFormStart()
    {
        return static::callHook('admin.login.form.start');
    }

    /**
     * Хук: поля формы авторизации (например, выбор языка)
     * Используется в resources/views/login.blade.php
     */
    public static function loginFormFields()
    {
        return static::callHook('admin.login.form.fields');
    }

    /**
     * Хук: конец формы авторизации
     * Используется в resources/views/auth/login.blade.php
     */
    public static function loginFormEnd()
    {
        return static::callHook('admin.login.form.end');
    }

    /**
     * Хук: скрипты для формы авторизации
     * Используется в @push('scripts') в resources/views/auth/login.blade.php
     */
    public static function loginFormScripts()
    {
        return static::callHook('admin.login.form.scripts');
    }

    /**
     * Хук: левая часть навбара
     * Используется в resources/views/layouts/admin.blade.php
     */
    public static function navbarLeft()
    {
        return static::callHook('admin.navbar.left');
    }

    /**
     * Хук: правая часть навбара (например, переключатель языка)
     * Используется в resources/views/layouts/admin.blade.php
     */
    public static function navbarRight()
    {
        return static::callHook('admin.navbar.right');
    }

    /**
     * Регистрация слушателя события
     * Используется в пакетах для подписки на хуки
     */
    public static function listen($event, $callback)
    {
        Event::listen($event, $callback);
    }

    public static function pageHeader()
    {
        return static::callHook('admin.page.header');
    }

    public static function pageFooter()
    {
        return static::callHook('admin.page.footer');
    }
}
