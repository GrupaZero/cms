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
            return [];
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
     * @param string $categoryKey category key
     * @param string $optionKey   option key
     *
     * @return array
     */
    function option($categoryKey, $optionKey)
    {
        return app('options')->getOption($categoryKey, $optionKey);
    }
}
