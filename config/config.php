<?php
return [
    'domain'                   => env('DOMAIN', 'localhost'),
    'app_version'              => env('APP_VERSION', 'latest'),
    'default_page_size'        => 5,
    'seo'                      => [
        'alternative_title' => 'title',
        'alternative_desc'  => 'body',
        'desc_length'       => 160
    ],
    'image'                    => [
        'max_width'  => 1920,
        'max_height' => 1080,
        'thumb'      => [
            'width'  => 729,
            'height' => 459
        ],
    ],
    'use_users_nicks'          => env('USE_USERS_NICKS', true),
    'multilang'                => [
        'enabled'   => env('MULTILANG_ENABLED', true),
        'detected'  => false, // Do not change, changes in runtime!
        'subdomain' => env('MULTILANG_SUBDOMAIN', false)
    ],
    'available_blocks_regions' => [
        'header',
        'homepage',
        'featured',
        'contentHeader',
        'sidebarLeft',
        'sidebarRight',
        'contentFooter',
        'footer'
    ],
    'disqus'                   => [
        'api_key'    => env('DISQUS_API_KEY', ''),
        'api_secret' => env('DISQUS_API_SECRET', ''),
        'domain'     => env('DISQUS_DOMAIN', ''), //<DISQUS_DOMAIN>.disqus.com/embed.js
        'enabled'    => env('DISQUS_ENABLED', 'false'),
    ]
];
