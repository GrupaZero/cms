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
        'files'     => [
            'lang'      => 'required_with:sort|in:pl,en,de,fr',
            'page'      => 'numeric',
            'per_page'  => 'numeric',
            'type'      => 'in:image,document',
            'is_active' => 'boolean',
        ],
        'create'    => [
            'type'               => 'required|in:content,category',
            'language_code'      => 'required|in:pl,en,de,fr',
            'title'              => 'required',
            'teaser'             => '',
            'body'               => '',
            'seo_title'          => '',
            'seo_description'    => '',
            'is_active'          => 'boolean',
            'parent_id'          => 'numeric|nullable',
            'published_at'       => 'date',
            'is_on_home'         => 'boolean',
            'is_promoted'        => 'boolean',
            'is_sticky'          => 'boolean',
            'is_comment_allowed' => 'boolean',
            'theme'              => '',
            'weight'             => 'numeric'
        ],
        'update'    => [
            'parent_id'          => 'numeric|nullable',
            'thumb_id'           => 'numeric|nullable',
            'weight'             => 'numeric',
            'rating'             => 'numeric',
            'theme'              => '',
            'is_on_home'         => 'boolean',
            'is_comment_allowed' => 'boolean',
            'is_promoted'        => 'boolean',
            'is_sticky'          => 'boolean',
            'published_at'       => 'date',
        ],
        'syncFiles' => [
            'data'          => 'present|array',
            'data.*.id'     => 'numeric',
            'data.*.weight' => 'numeric',
        ]
    ];

}
