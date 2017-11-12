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
        'basic'  => Gzero\Cms\Handler\Block\Basic::class,
        'menu'   => Gzero\Cms\Handler\Block\Menu::class,
        'slider' => Gzero\Cms\Handler\Block\Slider::class,
        'widget' => Gzero\Cms\Handler\Block\Widget::class
    ],
    'content_type'             => [
        'content'  => Gzero\Cms\Handler\Content\Content::class,
        'category' => Gzero\Cms\Handler\Content\Category::class
    ],
    'file_type'                => [
        'image'    => Gzero\Cms\Handler\File\Image::class,
        'document' => Gzero\Cms\Handler\File\Document::class,
        'video'    => Gzero\Cms\Handler\File\Video::class,
        'music'    => Gzero\Cms\Handler\File\Music::class
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
