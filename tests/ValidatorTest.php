<?php

require_once(__DIR__ . '/Gzero/Stub/DummyValidator.php');

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ValidatorTest
 *
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

    protected $input;

    protected function setUp()
    {
        $this->input = $this->initData();
    }

    /**
     * @test
     */
    public function isInstantiable()
    {
        $this->assertInstanceOf('\Gzero\validator\AbstractValidator', new DummyValidator($this->input));
    }

    /**
     * @expectedException Gzero\Validator\ValidationException
     */
    public function testValidationErrors()
    {
        try {
            $validator = new DummyValidator();
            $validator->validate('list');
        } catch (Gzero\Validator\ValidationException $e) {
            $this->assertEquals('The lang field is required.', $e->getErrors()->first('lang'));
            throw $e;
        }
    }

    /**
     * @expectedException Gzero\Validator\ValidationException
     */
    public function testBindRules()
    {
        try {
            $validator = new DummyValidator($this->input);
            $validator->bind('lang', ['required' => 'numeric'])->validate('update');
        } catch (Gzero\Validator\ValidationException $e) {
            $this->assertEquals('The lang must be a number.', $e->getErrors()->first('lang'));
            throw $e;
        }
    }

    public function testOnlyWithRulesAttributes()
    {

        $fakeInput = [
            'testAttribute1' => 'dummyValue1',
            'testAttribute2' => 'dummyValue2'
        ];

        $input     = array_merge($this->input, $fakeInput);
        $validator = new DummyValidator($input);
        $data      = $validator->validate('list');
        $this->assertEquals($this->input, $data);
    }

    public function testFilters()
    {
        $this->input['title'] = 'Lorem Ipsum        ';

        $validator = new DummyValidator($this->input);
        $this->assertNotEquals($this->input, $validator->validate('list'));
    }

    /**
     * @return array
     */
    protected function initData()
    {
        return [
            'lang'     => 'en',
            'title'    => 'Lorem Ipsum',
            'type'     => 'content',
            'parentId' => 1,
            'level'    => 0
        ];

    }

}
