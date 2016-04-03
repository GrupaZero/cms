<?php namespace Gzero\Validator;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BaseUserValidator
 *
 * @package    Gzero\Validator
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class BaseUserValidator extends AbstractValidator {

    /**
     * @var array
     */
    protected $rules = [
        'login'    => [
            'email'    => 'required|email',
            'password' => 'required'
        ],
        'register' => [
            'email'                 => 'required|email|unique:Users',
            'nickName'              => 'required|min:3|unique:Users',
            'password'              => 'required|min:6',
            'firstName'             => 'min:2|regex:/^([^0-9]*)$/', // without numbers
            'lastName'              => 'min:2|regex:/^([^0-9]*)$/' // without numbers
        ],
        'remind'   => [
            'email' => 'required|email',
        ],
        'reset'    => [
            'email'                 => 'required|email',
            'password'              => 'required|min:6|same:password_confirmation',
            'password_confirmation' => 'required|min:6|same:password',
            'token'                 => '',
        ],
        'update'   => [
            'nickName'              => 'required|min:3|unique:Users,nickName,@userId',
            'firstName'             => 'min:2|regex:/^([^0-9]*)$/', // without numbers
            'lastName'              => 'min:2|regex:/^([^0-9]*)$/', // without numbers
            'password'              => 'sometimes|min:6|same:password_confirmation|required_with:password_confirmation',
            'password_confirmation' => 'sometimes|min:6|same:password|required_with:password',
        ],
    ];

    /**
     * @var array
     */
    protected $filters = [
    ];
}
