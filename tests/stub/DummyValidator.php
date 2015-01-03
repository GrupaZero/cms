<?php

use Gzero\Validator\AbstractValidator;

/**
 * Class DummyValidator
 */
class DummyValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'list'   => [
            'lang'              => 'required',
            'page'              => 'numeric',
            'perPage'           => 'numeric',
            'type'              => 'in:content,category',
            'parentId'          => 'numeric',
            'level'             => '',
            'title'             => '',
            'translation.test1' => 'required',
            'translation.test2' => 'numeric'
        ],
        'update' => [
            'lang' => '@required'
        ]
    ];

    /**
     * @var array
     */
    protected $filters = [
        'title'             => 'trim',
        'translation.test1' => 'trim',
    ];

}
