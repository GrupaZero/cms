<?php namespace Gzero\Cms\Validators;

use Gzero\Core\Validators\AbstractValidator;

class ContentValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'tree'      => [
            'lang'      => 'required_with:sort|in:pl,en,de,fr',
            'type'      => 'in:category',
            'weight'    => 'numeric',
            'is_active' => 'boolean',
            'sort'      => ''
        ],
        'list'      => [
            'lang'      => 'required_with:sort|in:pl,en,de,fr',
            'page'      => 'numeric',
            'per_page'  => 'numeric',
            'type'      => 'in:content,category',
            'parent_id' => 'numeric',
            'is_active' => 'boolean',
            'sort'      => '',
            'level'     => '',
            'trashed'   => ''
        ],
        'files'     => [
            'lang'      => 'required_with:sort|in:pl,en,de,fr',
            'page'      => 'numeric',
            'per_page'  => 'numeric',
            'type'      => 'in:image,document',
            'is_active' => 'boolean',
        ],
        'create'    => [
            'type'                         => 'required|in:content,category',
            'parent_id'                    => 'numeric|nullable',
            'weight'                       => 'numeric',
            'theme'                        => '',
            'is_on_home'                   => 'boolean',
            'is_comment_allowed'           => 'boolean',
            'is_promoted'                  => 'boolean',
            'is_sticky'                    => 'boolean',
            'is_active'                    => 'boolean',
            'published_at'                 => 'date|date_format:Y-m-d H:i:s',
            'translations.lang_code'       => 'required|in:pl,en,de,fr',
            'translations.title'           => 'required',
            'translations.teaser'          => '',
            'translations.body'            => '',
            'translations.seo_title'       => '',
            'translations.seo_description' => ''
        ],
        'update'    => [
            'parent_id'          => 'numeric|nullable',
            'thumb_id'           => 'numeric|nullable',
            'weight'             => 'numeric',
            'theme'              => '',
            'is_active'          => 'boolean',
            'is_on_home'         => 'boolean',
            'is_comment_allowed' => 'boolean',
            'is_promoted'        => 'boolean',
            'is_sticky'          => 'boolean',
            'published_at'       => 'date|date_format:Y-m-d H:i:s',
        ],
        'syncFiles' => [
            'data'          => 'present|array',
            'data.*.id'     => 'numeric',
            'data.*.weight' => 'numeric',
        ]
    ];

}
