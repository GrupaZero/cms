<?php

if (!function_exists('setMultilangRouting')) {
    function setMultilangRouting()
    {
        if (Config::get('gzero.multilang.enabled')) {
            if (Config::get('gzero.multilang.subdomain')) {
                if (Config::get('gzero.multilang.detected')) {
                    return array('domain' => Request::getHost());
                } else {
                    return array('domain' => App::getLocale() . '.' . Request::getHost());
                }
            } else {
                return array('prefix' => App::getLocale());
            }
        } else {
            return array();
        }
    }
}
