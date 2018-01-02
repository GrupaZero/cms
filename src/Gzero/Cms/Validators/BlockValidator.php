<?php namespace Gzero\Cms\Validators;

use Gzero\Core\Validators\AbstractValidator;

class BlockValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'files'       => [
            'lang'      => 'required_with:sort|in:pl,en,de,fr',
            'page'      => 'numeric',
            'per_page'  => 'numeric',
            'type'      => 'in:image,document',
            'is_active' => 'boolean',
        ],
        'create'      => [
            'type'          => 'required|in:basic,menu,slider,widget',
            'title'         => 'required',
            'language_code' => 'required|in:pl,en,de,fr',
            'region'        => '',
            'theme'         => '',
            'weight'        => 'numeric',
            'filter'        => 'array',
            'options'       => 'array',
            'widget'        => '',
            'is_cacheable'  => 'boolean',
            'is_active'     => 'boolean',
            'body'          => '',
            'custom_fields' => 'array',
        ],
        'update'      => [
            'region'       => '',
            'theme'        => '',
            'weight'       => 'numeric',
            'filter'       => '',
            'options'      => '',
            'widget'       => '',
            'is_cacheable' => 'boolean',
            'is_active'    => 'boolean',
        ],
        'syncFiles'   => [
            'data'          => 'present|array',
            'data.*.id'     => 'numeric',
            'data.*.weight' => 'numeric',
        ]
    ];

    /**
     * @var array
     */
    protected $filters = [
        'title' => 'trim'
    ];
}
