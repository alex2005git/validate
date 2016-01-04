<?php

namespace Phramework\Validate;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-05 at 20:04:03.
 */
class UnsignedIntegerValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UnsignedIntegerValidator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new UnsignedIntegerValidator(10, 1000, true);
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
            [124, 124]
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['-0x'],
            ['abc'],
            ['+xyz']
            [-1000],
            ['-4'],
            [4], //because of min,
            [1.4],
            [-13.5]
        ];
    }

    /**
     * @covers Phramework\Validate\UnsignedIntegerValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new UnsignedIntegerValidator(
            0,
            1
        );
    }

    /**
     * @covers Phramework\Validate\UnsignedIntegerValidator::__construct
     * @expectedException Exception
     */
    public function testConstructFailure()
    {
        $validator = new UnsignedIntegerValidator(
            -1
        );
    }

    /**
     * Helper method
     */
    private function validateSuccess(UnsignedIntegerValidator $object, $input, $expected)
    {
        $return = $object->validate($input);

        $this->assertTrue($return->status);
        $this->assertInternalType('integer', $return->value);
        $this->assertSame($expected, $return->value);
    }

    /**
     * @covers Phramework\Validate\UnsignedIntegerValidator::validate
     * @dataProvider validateSuccessProvider
     */
    public function testValidateSuccess($input, $expected)
    {
        $this->validateSuccess($this->object, $input, $expected);
    }

    /**
     * @covers Phramework\Validate\UnsignedIntegerValidator::createFromJSON
     */
    public function testCreateFromJSON()
    {
        $json = '{
            "type": "unsignedinteger"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UnsignedIntegerValidator::class, $validationObject);
    }

    /**
     * @covers Phramework\Validate\UnsignedIntegerValidator::createFromJSON
     */
    public function testCreateFromJSONAlias()
    {
        $json = '{
            "type": "uint"
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(UnsignedIntegerValidator::class, $validationObject);
    }

    /**
     * @covers Phramework\Validate\UnsignedIntegerValidator::getType
     */
    public function testGetType()
    {
        $this->assertEquals('unsignedinteger', $this->object->getType());
    }
}