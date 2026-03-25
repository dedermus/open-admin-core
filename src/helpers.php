<?php

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\MessageBag;

if (!function_exists('admin_path')) {
    /**
     * Get admin path.
     *
     * @param string $path
     *
     * @return string
     */
    function admin_path($path = '')
    {
        return ucfirst(config('admin.directory')).($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('admin_url')) {
    /**
     * Get admin url.
     *
     * @param string $path
     * @param mixed  $parameters
     * @param bool   $secure
     *
     * @return string
     */
    function admin_url($path = '', $parameters = [], $secure = null)
    {
        if (\Illuminate\Support\Facades\URL::isValidUrl($path)) {
            return $path;
        }

        // Sanitize path
        $path = trim($path, '/');
        $path = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $path);

        $secure = $secure ?: (config('admin.https') || config('admin.secure'));

        return url(admin_base_path($path), $parameters, $secure);
    }
}

if (!function_exists('admin_base_path')) {
    /**
     * Get admin url.
     *
     * @param string $path
     *
     * @return string
     */
    function admin_base_path($path = '')
    {
        $prefix = '/'.trim(config('admin.route.prefix'), '/');

        $prefix = ($prefix == '/') ? '' : $prefix;

        $path = trim($path, '/');

        if (is_null($path) || strlen($path) == 0) {
            return $prefix ?: '/';
        }

        return $prefix.'/'.$path;
    }
}

if (!function_exists('admin_toastr')) {
    /**
     * Flash a toastr message bag to session.
     *
     * @param string $message
     * @param string $type
     * @param array  $options
     */
    function admin_toastr($message = '', $type = 'success', $options = [])
    {
        $toastr = new MessageBag(get_defined_vars());

        session()->flash('toastr', $toastr);
    }
}

if (!function_exists('admin_success')) {
    /**
     * Flash a success message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_success($title, $message = '')
    {
        admin_info($title, $message, 'success');
    }
}

if (!function_exists('admin_error')) {
    /**
     * Flash a error message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_error($title, $message = '')
    {
        admin_info($title, $message, 'error');
    }
}

if (!function_exists('admin_warning')) {
    /**
     * Flash a warning message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_warning($title, $message = '')
    {
        admin_info($title, $message, 'warning');
    }
}

if (!function_exists('admin_info')) {
    /**
     * Flash a message bag to session.
     *
     * @param string $title
     * @param string $message
     * @param string $type
     */
    function admin_info($title, $message = '', $type = 'info')
    {
        $message = new MessageBag(get_defined_vars());

        session()->now($type, $message);
    }
}

if (!function_exists('admin_asset')) {
    /**
     * @param $path
     *
     * @return string
     */
    function admin_asset($path)
    {
        return (config('admin.https') || config('admin.secure')) ? secure_asset($path) : asset($path);
    }
}

if (!function_exists('admin_trans')) {
    /**
     * Translate the given message.
     *
     * @param string|null $key
     * @param array       $replace
     * @param string|null $locale
     *
     * @return string|null
     */
    function admin_trans(string $key = null, array $replace = [], string $locale = null)
    {
        $line = __($key, $replace, $locale);

        if (!is_string($line) || $line === $key) {
            // Fallback to admin translation file
            $adminKey = "admin.{$key}";
            $line = __($adminKey, $replace, $locale);

            if (!is_string($line) || $line === $adminKey) {
                return $key;
            }
        }

        return $line;
    }
}

if (!function_exists('array_delete')) {
    /**
     * Delete from array by value.
     *
     * @param array $array
     * @param mixed $value
     */
    function array_delete(&$array, $value)
    {
        $value = \Illuminate\Support\Arr::wrap($value);

        foreach ($array as $index => $item) {
            if (in_array($item, $value)) {
                unset($array[$index]);
            }
        }
    }
}

if (!function_exists('class_uses_deep')) {
    /**
     * To get ALL traits including those used by parent classes and other traits.
     *
     * @param $class
     * @param bool $autoload
     *
     * @return array
     */
    function class_uses_deep($class, $autoload = true)
    {
        $traits = [];

        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }
}

if (!function_exists('admin_dump')) {
    /**
     * @param $var
     *
     * @return string
     */
    function admin_dump($var)
    {
        ob_start();

        dump(...func_get_args());

        $contents = ob_get_contents();

        ob_end_clean();

        return $contents;
    }
}

if (!function_exists('file_size')) {
    /**
     * Convert file size to a human readable format like `100mb`.
     *
     * @param int $bytes
     *
     * @return string
     *
     * @see https://stackoverflow.com/a/5501447/9443583
     */
    function file_size($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2).' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes.' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes.' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}

if (!function_exists('admin_csrf_token')) {
    /**
     * Get CSRF token for admin forms
     */
    function admin_csrf_token(): string
    {
        return csrf_token();
    }
}

if (!function_exists('admin_csrf_field')) {
    /**
     * Generate CSRF field for admin forms
     */
    function admin_csrf_field(): string
    {
        return csrf_field();
    }
}

if (!function_exists('prepare_safe_options')) {
    /**
     * Safe preparation of options for frontend with callback validation
     */
    function prepare_safe_options(array $options): array
    {
        $allowedCallbacks = [
            'refresh', 'submit', 'cancel', 'validate', 'success', 'error',
            'load', 'save', 'delete', 'update', 'create'
        ];

        return array_map(function ($value) use ($allowedCallbacks) {
            if (is_array($value)) {
                return prepare_safe_options($value);
            }

            if (is_string($value) && strpos($value, 'function(') === 0) {
                // Extract simple callback name for safe reference
                if (preg_match('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)/', $value, $matches)) {
                    $callbackName = $matches[1] ?? 'defaultHandler';
                } else {
                    $callbackName = 'anonymous';
                }

                // Return safe callback reference
                return [
                    '_type' => 'js_callback',
                    'handler' => in_array($callbackName, $allowedCallbacks) ? $callbackName : 'defaultHandler'
                ];
            }

            return $value;
        }, $options);
    }
}

if (!function_exists('safe_json_encode_options')) {
    /**
     * Safe JSON encoding with callback validation
     */
    function safe_json_encode_options(array $options): string
    {
        $safeOptions = prepare_safe_options($options);
        return json_encode($safeOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
}

if (!function_exists('migrate_old_options')) {
    /**
     * Migration helper for old options format
     */
    function migrate_old_options(array $oldOptions): array
    {
        $migrated = [];

        foreach ($oldOptions as $key => $value) {
            if (is_array($value)) {
                $migrated[$key] = migrate_old_options($value);
            } elseif (is_string($value) && strpos($value, 'function(') === 0) {
                // Mark for manual review
                $migrated[$key] = [
                    '_type' => 'needs_migration',
                    '_original' => $value,
                    '_message' => 'This callback needs manual migration'
                ];
            } else {
                $migrated[$key] = $value;
            }
        }

        return $migrated;
    }
}

if (!function_exists('admin_get_route')) {
    function admin_get_route(string $name): string
    {
        return config('admin.route.prefix').'.'.$name;
    }
}
