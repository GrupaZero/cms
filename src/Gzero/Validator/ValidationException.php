<?php namespace Gzero\Validator;

use Gzero\Core\Exception;
use Illuminate\Support\MessageBag;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ValidationException
 *
 * @package    Gzero\Validator
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class ValidationException extends Exception {

    /**
     * @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * ValidationException constructor
     *
     * @param MessageBag $errors Container with validation errors
     */
    public function __construct(MessageBag $errors = null)
    {
        $this->errors = $errors;
        $this->buildMessage();
    }

    /**
     * Return validation errors
     *
     * @return MessageBag
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add Validation errors
     *
     * @param MessageBag $errors Container with validation errors
     *
     * @return void
     */
    public function addErrors(MessageBag $errors)
    {
        $this->errors->merge($errors);
        $this->buildMessage();
    }

    /**
     * Build simple message
     *
     * @return void
     */
    protected function buildMessage()
    {
        $this->message = $this->errors->toJson();
    }
}
