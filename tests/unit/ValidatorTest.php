<?php namespace unit;

require_once(__DIR__ . '/../TestCase.php');
require_once(__DIR__ . '/../stub/DummyValidator.php');

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
class ValidatorTest extends \TestCase {

    /**
     * @var Array
     */
    protected $input;

    /**
     * @var DummyValidator
     */
    protected $validator;

    public function setUp()
    {
        parent::setUp();
        $this->input     = $this->initData();
        $this->validator = new \DummyValidator(\App::make('validator'));
    }

    /**
     * @test
     * @expectedException Gzero\Validator\ValidationException
     */
    public function it_throws_exceptions_with_errors()
    {
        try {
            $this->input['type'] = 'product';
            $this->validator->validate('list', $this->input);
        } catch (Gzero\Validator\ValidationException $e) {
            $this->assertEquals('validation.in', $e->getErrors()->first('type'));
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException Gzero\Validator\ValidationException
     */
    public function can_bind_rules()
    {
        try {
            $this->validator->bind('lang', ['required' => 'numeric'])->validate('update', $this->input);
        } catch (Gzero\Validator\ValidationException $e) {
            $this->assertEquals('validation.numeric', $e->getErrors()->first('lang'));
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException Gzero\Core\Exception
     */
    public function it_checks_validation_context()
    {
        $this->validator->validate('fakeContext', []);
    }

    /**
     * @test
     */
    public function only_fields_in_rules_are_returned()
    {
        $fakeInput = [
            'testAttribute1' => 'dummyValue1',
            'testAttribute2' => 'dummyValue2',
            'testAttribute3' => [
                'test' => 'dummyValue3'
            ],
        ];
        $input     = array_merge($this->input, $fakeInput);
        $data      = $this->validator->validate('list', $input);
        $this->assertContains('pl', $data);
        $this->assertArrayHasKey('level', $data);
        $this->assertEquals(0, $data['level']);
        $this->assertContains(['test1' => 'Before trim', 'test2' => 2], $data); // Testing nested array & trim filter
    }

    /**
     * @test
     */
    public function it_apply_filters()
    {
        $this->input['title'] = 'Lorem Ipsum        ';
        $this->assertNotEquals($this->input, $this->validator->validate('list', $this->input));
    }

    /**
     * @return array
     */
    protected function initData()
    {
        return [
            'title'       => 'Lorem Ipsum',
            'type'        => 'content',
            'lang'        => 'pl',
            'parentId'    => null,
            'translation' => [
                'test1' => 'Before trim       ',
                'test2' => 2
            ],
            'level'       => 0
        ];

    }

}
