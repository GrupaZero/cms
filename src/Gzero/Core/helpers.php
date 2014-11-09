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
                    return ['domain' => App::getLocale() . '.' . Request::getHost()];
                }
            } else {
                return ['prefix' => App::getLocale()];
            }
        } else {
            return [];
        }
    }
}
