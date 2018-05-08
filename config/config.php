<?php
return [
    'seo'                      => [
        'alternative_title' => 'title',
        'alternative_desc'  => 'body'
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
