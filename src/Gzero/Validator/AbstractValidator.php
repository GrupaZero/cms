<?php namespace Gzero\Validator;

use Gzero\Core\Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Factory;

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
     * Data to validation
     *
     * @var array
     */
    protected $data = [];

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
     * @param Factory $validator Validator factory
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate passed data
     *
     * @param string $context Validation context
     * @param Array  $data    Data to validate
     *
     * @throws Exception
     * @throws ValidationException
     * @return array
     */
    public function validate($context = 'default', Array $data = [])
    {
        if (!empty($data)) {
            $this->setData($data);
        }

        $this->setContext($context);
        $rules = $this->buildRulesArray();
        $this->setValidator(Validator::make($this->filterArray($rules, $this->data), $rules));
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
     * Set data to validate
     *
     * @param array $data Data to validate
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
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
     * Return laravel validator
     *
     * @return Validator
     */
    protected function getValidator()
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
    protected function setValidator($validator)
    {
        $this->validator = $validator;
    }

    /**
     * Set validation context
     *
     * @param string $context Validation context
     *
     * @return void
     */
    protected function setContext($context)
    {
        $this->context = $context;
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
            $value = array_get($rawAttributes, $filedName, 'not found in array'); // Default value !== null
            if ($value !== 'not found in array') { // Only if field specified in incoming array
                if (isset($this->filters[$filedName])) {
                    $filters = explode('|', $this->filters[$filedName]);
                    foreach ($filters as $filter) {
                        array_set($attributes, $filedName, $this->$filter($value));
                    }
                } else {
                    array_set($attributes, $filedName, $value);
                }
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
