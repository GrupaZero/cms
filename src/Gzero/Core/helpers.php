<?php

use Illuminate\Support\Str;

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

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string $title     Title to slug
     * @param string $separator Seperator
     *
     * @return string
     */
    function str_slug($title, $separator = '-')
    {
        return Str::slug($title, $separator);
    }
}
