<?php

namespace Phramework\Validate;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 20:04:03.
 */
class IntegerValidatorTest extends TestCase
{

    /**
     * @var IntegerValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new IntegerValidator(-1000, 1000, true);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function validateSuccessProvider()
    {
        //input, expected
        return [
            ['100', 100],
            [124, 124],
            [0, 0],
            [-10, -10],
            [-99, -99]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['-0x'],
            ['abc'],
            ['+xyz'],
            ['++30'],
            [-1000], //should fail becaus of exclusiveMinimum
            [-10000000],
            [10000000],
            ['-1000000000'],
            [1.4],
            [-13.5]
        ];
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new IntegerValidator(
            0,
            1
        );
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::__construct
     * @expectedException Exception
     */
    public function testConstructFailure()
    {
        $validator = new IntegerValidator(
            'a'
        );
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::__construct
     * @expectedException Exception
     */
    public function testConstructFailure2()
    {
        $validator = new IntegerValidator(
            1,
            'a'
        );
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::__construct
     * @expectedException Exception
     */
    public function testConstructFailure3()
    {
        $validator = new IntegerValidator(
            1,
            2,
            null,
            null,
            'a'
        );
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::createFromJSON
     * @dataProvider validateSuccessProvider
     */
    public function testCreateFromJSON($input, $expected)
    {
        $json = '{
            "type": "integer",
            "minimum" : -1000,
            "maximum" : 1000,
            "title": "my int",
            "default": 10,
            "x-extra": "not existing"
        }';

        $validatorObject = IntegerValidator::createFromJSON($json);

        $this->assertSame(
            'my int',
            $validatorObject->title,
            'Title must be passed'
        );

        $this->assertSame(
            10,
            $validatorObject->default,
            'Default must be passed'
        );

        $this->assertObjectNotHasAttribute(
            'x-extra',
            $validatorObject,
            'Attribute must not exists'
        );

        //use helper function to validate $input against this validator
        $this->validateSuccess($validatorObject, $input, $expected);
    }

    /**
     * Helper method
     */
    private function validateSuccess(IntegerValidator $object, $input, $expected)
    {
        $return = $object->validate($input);

        $this->assertTrue($return->status);
        $this->assertInternalType('integer', $return->value);
        $this->assertSame($expected, $return->value);
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::validate
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $this->validateSuccess($this->object, $input, $expected);
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::validate
     * @dataProvider validateFailureProvider
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);

        $this->markTestIncomplete(
            'Test Exclusive'
        );
    }

    /**
     * @covers Phramework\Validate\NumberValidator::validate
     */
    public function testValidateFailureMultipleOf()
    {
        $validator = new IntegerValidator(null, null, null, null, 2);
        $return = $validator->validate(5);

        $this->assertFalse($return->status);

        $parameters = $return->errorObject->getParameters();

        $this->assertEquals('multipleOf', $parameters[0]['failure']);
    }

    /**
     * Validate against common enum keyword
     * @covers Phramework\Validate\IntegerValidator::validateEnum
     */
    public function testValidateCommon()
    {
        $validator = (new IntegerValidator(0, 10));

        $validator->enum = [1, 3, 5];

        $return = $validator->validate(1);
        $this->assertTrue(
            $return->status,
            'Expect true since "1" is in enum array'
        );

        $return = $validator->validate(2);
        $this->assertFalse(
            $return->status,
            'Expect false since "2" is not in enum array'
        );
    }

    /**
     * @covers Phramework\Validate\IntegerValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('integer', $this->object->getType());
    }
}
