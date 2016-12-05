<?php

if (!function_exists('setMultilangRouting')) {

    /**
     * Returns routing array with lang prefix
     *
     * @param array $routingOptions Additional routing options
     *
     * @return array
     */
    function setMultilangRouting(array $routingOptions = [])
    {
        if (config('gzero.multilang.enabled')) {
            if (config('gzero.multilang.subdomain')) {
                if (config('gzero.multilang.detected')) {
                    return array_merge(
                        $routingOptions,
                        ['domain' => request()->getHost()]
                    );
                } else {
                    return array_merge(
                        $routingOptions,
                        ['domain' => app()->getLocale() . '.' . config('gzero.domain')]
                    );
                }
            } else {
                return array_merge(
                    $routingOptions,
                    ['domain' => config('gzero.domain'), 'prefix' => app()->getLocale()]
                );
            }
        } else {
            // Set domain for static pages block finder
            return array_merge(
                $routingOptions,
                ['domain' => config('gzero.domain')]
            );
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
