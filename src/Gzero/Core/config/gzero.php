<?php
return [
    'domain'                   => env('DOMAIN', 'localhost'),
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
    'upload'                   => [
        'disk'                    => env('UPLOAD_DISK', 'uploads'),
        'allowed_file_extensions' => [
            'image'    => ['png', 'jpg', 'jpeg', 'tif'],
            'document' => ['pdf', 'odt', 'ods', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
            'video'    => ['mp4'],
            'music'    => ['mp3']
        ],

    ],
    'block_type'               => [
        'basic'   => 'Gzero\Core\Handler\Block\Basic',
        'content' => 'Gzero\Core\Handler\Block\Content',
        'menu'    => 'Gzero\Core\Handler\Block\Menu',
        'slider'  => 'Gzero\Core\Handler\Block\Slider',
        'widget'  => 'Gzero\Core\Handler\Block\Widget'
    ],
    'content_type'             => [
        'content'  => 'Gzero\Core\Handler\Content\Content',
        'category' => 'Gzero\Core\Handler\Content\Category'
    ],
    'file_type'                => [
        'image'    => 'Gzero\Core\Handler\File\Image',
        'document' => 'Gzero\Core\Handler\File\Document',
        'video'    => 'Gzero\Core\Handler\File\Video',
        'music'    => 'Gzero\Core\Handler\File\Music'
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
    ]
];
