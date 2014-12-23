<?php

use Illuminate\Validation\Factory;

require_once(__DIR__ . '/TestCase.php');
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
class ValidatorTest extends TestCase {

    /**
     * @var Array
     */
    protected $input;
    /**
     * @var Factory
     */
    protected $laravelValidator;

    public function setUp()
    {
        parent::setUp();
        $this->input            = $this->initData();
        $this->laravelValidator = \App::make('validator');
    }

    /**
     * @test
     */
    public function isInstantiable()
    {
        $this->assertInstanceOf('\Gzero\validator\AbstractValidator', new DummyValidator($this->laravelValidator));
    }

    /**
     * @expectedException Gzero\Validator\ValidationException
     */
    public function testValidationErrors()
    {
        try {
            $validator = new DummyValidator($this->laravelValidator);
            $validator->validate($this->input, 'list');
        } catch (Gzero\Validator\ValidationException $e) {
            $this->assertEquals('validation.required', $e->getErrors()->first('lang'));
            throw $e;
        }
    }

    ///**
    // * @expectedException Gzero\Validator\ValidationException
    // */
    //public function testBindRules()
    //{
    //    try {
    //        $validator = new DummyValidator($this->laravelValidator);
    //        $validator->bind('lang', ['required' => 'numeric'])->validate($this->input, 'update');
    //    } catch (Gzero\Validator\ValidationException $e) {
    //        $this->assertEquals('validation.numeric', $e->getErrors()->first('lang'));
    //        throw $e;
    //    }
    //}

    //public function testOnlyWithRulesAttributes()
    //{
    //
    //    $fakeInput = [
    //        'testAttribute1' => 'dummyValue1',
    //        'testAttribute2' => 'dummyValue2'
    //    ];
    //
    //    $input     = array_merge($this->input, $fakeInput);
    //    $validator = new DummyValidator($this->laravelValidator);
    //    $data      = $validator->validate($input, 'list');
    //    $this->assertEquals($this->input, $data);
    //}
    //
    //public function testFilters()
    //{
    //    $this->input['title'] = 'Lorem Ipsum        ';
    //
    //    $validator = new DummyValidator($this->laravelValidator);
    //    $this->assertNotEquals($this->input, $validator->validate($this->input, 'list'));
    //}

    /**
     * @return array
     */
    protected function initData()
    {
        return [
            'title'    => 'Lorem Ipsum',
            'type'     => 'content',
            'parentId' => 1,
            'level'    => 0
        ];

    }

}
