<?php namespace Gzero\Cms\Validators;

use Gzero\Core\Validators\AbstractValidator;

class ContentTranslationValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'create' => [
            'language_code'   => 'required|in:pl,en,de,fr',
            'is_active'       => '',
            'title'           => 'required',
            'teaser'          => '',
            'body'            => '',
            'seo_title'       => '',
            'seo_description' => ''
        ]
    ];

    /**
     * @var array
     */
    protected $filters = [
        'title'           => 'trim',
        'seo_title'       => 'trim',
        'seo_description' => 'trim'
    ];
}
