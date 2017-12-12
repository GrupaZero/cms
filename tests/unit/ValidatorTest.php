<?php namespace unit;

require_once(__DIR__ . '/../stub/DummyValidator.php');

use Codeception\Test\Unit;
use DummyValidator;
use Illuminate\Validation\ValidationException;

class ValidatorTest extends Unit {

    /**
     * @var array
     */
    protected $input;

    /**
     * @var DummyValidator
     */
    protected $validator;

    protected function _before()
    {
        // Start the Laravel application
        $this->input     = $this->initData();
        $this->validator = new DummyValidator(resolve('validator'));
    }


    protected function _after()
    {
        // Stop the Laravel application
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function it_throws_exceptions_with_errors()
    {
        try {
            $this->input['type'] = 'product';
            $this->validator->validate('list', $this->input);
        } catch (ValidationException $e) {
            $this->assertEquals('The selected type is invalid.', $e->validator->getMessageBag()->first('type'));
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function can_bind_rules()
    {
        try {
            $this->validator->bind('lang', ['required' => 'numeric'])->validate('update', $this->input);
        } catch (ValidationException $e) {
            $this->assertEquals('The lang must be a number.', $e->validator->getMessageBag()->first('lang'));
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException \Gzero\InvalidArgumentException
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
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function it_throw_required_error_for_arrays()
    {
        $this->input['data'] = [[]];
        try {
            $this->validator->validate('testArrays', $this->input);
        } catch (ValidationException $e) {
            $this->assertEquals('The data.0.id field is required.', $e->validator->getMessageBag()->first('data.0.id'));
            throw $e;
        }
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function it_throw_numeric_error_for_arrays()
    {
        $this->input['data'] = [['id' => 1337], ['id' => 'asdas']];
        try {
            $this->validator->validate('testArrays', $this->input);
        } catch (ValidationException $e) {
            $this->assertEquals('The data.1.id must be a number.', $e->validator->getMessageBag()->first('data.1.id'));
            throw $e;
        }
    }

    /**
     * @test
     */
    public function it_validates_arrays()
    {
        $this->input['data'] = [['id' => 1337], ['id' => 999]];

        $result = $this->validator->validate('testArrays', $this->input);
        $this->assertNotEmpty($result);
        $this->assertContains(['id' => 1337], $result['data']);
        $this->assertContains(['id' => 999], $result['data']);
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
            'parent_id'   => null,
            'translation' => [
                'test1' => 'Before trim       ',
                'test2' => 2
            ],
            'level'       => 0
        ];

    }

}
