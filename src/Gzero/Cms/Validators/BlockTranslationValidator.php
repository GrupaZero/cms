<?php namespace Gzero\Cms\Validators;

use Gzero\Core\Validators\AbstractValidator;

class BlockTranslationValidator extends AbstractValidator {

    /** @var array */
    protected $rules = [
        'create' => [
            'language_code' => 'required|in:pl,en,de,fr',
            'is_active'     => '',
            'title'         => 'required',
            'body'          => '',
            'custom_fields' => '',
        ]
    ];

    /** @var array */
    protected $filters = [
        'title' => 'trim',
    ];
}
