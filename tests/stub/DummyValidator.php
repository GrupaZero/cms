<?php

use Gzero\Core\Validators\AbstractValidator;

class DummyValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'list'       => [
            'lang'              => 'required',
            'page'              => 'numeric',
            'perPage'           => 'numeric',
            'type'              => 'in:content,category',
            'parent_id'         => 'numeric|nullable',
            'level'             => '',
            'title'             => '',
            'translation.test1' => 'required',
            'translation.test2' => 'numeric'
        ],
        'update'     => [
            'lang' => '@required'
        ],
        'testArrays' => [
            'data'      => 'required',
            'data.*.id' => 'required|numeric'
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
