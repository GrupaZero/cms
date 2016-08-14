<?php

if (!function_exists('setMultilangRouting')) {

    /**
     * Returns routing array with lang prefix
     *
     * @return array
     */
    function setMultilangRouting()
    {
        if (Config::get('gzero.multilang.enabled')) {
            if (Config::get('gzero.multilang.subdomain')) {
                if (Config::get('gzero.multilang.detected')) {
                    return ['domain' => Request::getHost()];
                } else {
                    return ['domain' => App::getLocale() . '.' . Config::get('gzero.domain')];
                }
            } else {
                return ['domain' => Config::get('gzero.domain'), 'prefix' => App::getLocale()];
            }
        } else {
            // Set domain for static pages block finder
            return ['domain' => Config::get('gzero.domain')];
        }
    }
}

if (!function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    function cache()
    {
        $arguments = func_get_args();
        if (empty($arguments)) {
            return app('cache');
        }
        if (is_string($arguments[0])) {
            return app('cache')->get($arguments[0], isset($arguments[1]) ? $arguments[1] : null);
        }
        if (is_array($arguments[0])) {
            if (!isset($arguments[1])) {
                throw new Exception(
                    'You must set an expiration time when putting to the cache.'
                );
            }
            return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
        }
    }
}

if (!function_exists('group')) {
    /**
     * Create a route group with shared attributes.
     *
     * @param array    $attributes Attributes
     * @param \Closure $callback   Callback function
     *
     * @return void
     */
    function group($attributes, $callback)
    {
        app('router')->group($attributes, $callback);
    }
}

if (!function_exists('isProviderLoaded')) {
    /**
     * Check if specified provider is loaded
     *
     * @param string $provider name
     *
     * @return boolean
     */
    function isProviderLoaded($provider)
    {
        $loadedProviders = app()->getLoadedProviders();
        return isset($loadedProviders[$provider]);
    }
}

if (!function_exists('option')) {
    /**
     * Return single option
     *
     * @param string         $categoryKey category key
     * @param string         $optionKey   option key
     * @param boolean|string $fallback    fallback value
     * @param boolean|string $language    lang code
     *
     * @return array|false
     */
    function option($categoryKey, $optionKey, $fallback = false, $language = false)
    {
        $option   = app('options')->getOption($categoryKey, $optionKey);
        $language = $language ? $language : app()->getLocale();

        if (array_key_exists($language, $option)) {
            return $option[$language];
        } else {
            return $fallback ? $fallback : false;
        }
    }
}
