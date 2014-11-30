<?php namespace Gzero\Validator;

use Gzero\Core\Exception;
use Illuminate\Support\Facades\Validator;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class AbstractValidator
 *
 * @package    Gzero\Validator
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
abstract class AbstractValidator {

    /**
     * Attributes to validation.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $context;

    /**
     * @var array
     */
    protected $placeholder = [];

    /**
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * AbstractValidator constructor
     *
     * @param string $context    Validation context
     * @param array  $attributes Data to validate
     */
    public function __construct($context, $attributes)
    {
        $this->context    = $context;
        $this->attributes = $attributes;
    }

    /**
     * Factory method
     *
     * @param string $context    Validation context
     * @param array  $attributes Data to validate
     *
     * @return static
     */
    public static function make($context, $attributes)
    {
        return new static($context, $attributes);
    }

    /**
     * Validate passed data
     *
     * @return array
     * @throws ValidationException
     */
    public function validate()
    {
        $rules = $this->buildRulesArray();
        $this->setValidator(Validator::make($this->filterArray($rules, $this->attributes), $rules));
        if ($this->getValidator()->passes()) {
            return $this->getValidator()->getData();
        } else {
            throw new ValidationException($this->getValidator()->getMessageBag());
        }
    }

    /**
     * Bind value for placeholder
     *
     * @param string $key   placeholder
     * @param mixed  $value value to bind
     *
     * @return $this
     */
    public function bind($key, $value)
    {
        $this->placeholder[$key] = $value;
        return $this;
    }

    /**
     * Return laravel validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Set laravel validator
     *
     * @param \Illuminate\Validation\Validator $validator Laravel validator
     *
     * @return void
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    /**
     * Build-in filter
     *
     * @param mixed $value Vale to trim
     *
     * @return string
     */
    public function trim($value)
    {
        return trim($value);
    }

    /**
     * Build rules array
     *
     * @return array
     * @throws Exception
     */
    protected function buildRulesArray()
    {
        if (isset($this->rules[$this->context])) {
            return $this->bindPlaceholders($this->rules[$this->context]);
        } else {
            throw new Exception("Undefined validation context: " . $this->context);
        }
    }

    /**
     * Filter array with data to validate
     *
     * @param array $rules         Rules array
     * @param array $rawAttributes Array with data passed to validation
     *
     * @return array
     */
    protected function filterArray($rules, $rawAttributes)
    {
        $attributes = [];
        foreach (array_keys($rules) as $filedName) {
            if (isset($rawAttributes[$filedName])) { // Only if field specified in incoming array
                if (isset($this->filters[$filedName])) {
                    $filters = explode('|', $this->filters[$filedName]);
                    foreach ($filters as $filter) {
                        $attributes[$filedName] = $this->$filter($rawAttributes[$filedName]);
                    }
                } else {
                    $attributes[$filedName] = $rawAttributes[$filedName];
                }
            } elseif (preg_match('/^is/', $filedName)) {
                $attributes[$filedName] = 0;
            }
        }
        return $attributes;
    }

    /**
     * Bind placeholders
     *
     * @param array $rules Array with rules
     *
     * @return array
     */
    protected function bindPlaceholders(&$rules)
    {
        foreach ($rules as $name => &$rule) {
            if (isset($this->placeholder[$name])) {
                foreach ($this->placeholder[$name] as $bindName => $bind) {
                    $rule = preg_replace("/@$bindName/", $bind, $rule);
                }
            }
        }
        return $rules;
    }
}
