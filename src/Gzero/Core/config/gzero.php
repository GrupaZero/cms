<?php
return [
    'domain'                            => env('DOMAIN', 'localhost'),
    'site_name'                         => 'G-ZERO CMS',
    'site_desc'                         => 'Content management system.',
    'default_page_size'                 => 5,
    'seo' => [
        'alternative_title' => 'title',
        'alternative_desc'  => 'body',
        'desc_length'       => 160
    ],
    'use_users_nicks'                   => true,
    'multilang'                         => [
        'enabled'   => true,
        'detected'  => false, // Do not change, changes in runtime!
        'subdomain' => false
    ],
    'upload'                            => [
        'directory' => env('UPLOAD_DIR', 'uploads') // directory inside filesystem root directory (storage/app/ as default)
    ],
    'block_type'                        => [
        'basic'   => 'Gzero\Core\Handler\Block\Basic',
        'content' => 'Gzero\Core\Handler\Block\Content',
        'menu'    => 'Gzero\Core\Handler\Block\Menu',
        'slider'  => 'Gzero\Core\Handler\Block\Slider',
        'widget'  => 'Gzero\Core\Handler\Block\Widget'
    ],
    'content_type'                      => [
        'content'  => 'Gzero\Core\Handler\Content\Content',
        'category' => 'Gzero\Core\Handler\Content\Category'
    ],
    'file_type'                         => [
        'image'    => 'Gzero\Core\Handler\File\Image',
        'document' => 'Gzero\Core\Handler\File\Document',
        'video'    => 'Gzero\Core\Handler\File\Video',
        'music'    => 'Gzero\Core\Handler\File\Music'
    ],
    'allowed_file_extensions'           => [
        'image'    => ['png', 'jpg', 'jpeg', 'tif'],
        'document' => ['pdf', 'odt', 'ods', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
        'video'    => ['mp4'],
        'music'    => ['mp3']
    ],
    'available_blocks_regions'          => [
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
