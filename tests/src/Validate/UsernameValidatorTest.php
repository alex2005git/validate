<?php

namespace Phramework\Validate;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-05 at 22:11:07.
 */
class UsernameTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Username
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Username();
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
            ['nohponex'],
            ['NohponeX'],
            ['nohp_onex'],
            ['nohp_o.nex']
        ];
    }

    public function validateFailureProvider()
    {
        //input
        return [
            ['too short' =>  'ni'],
            ['too long' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'],
            ['invalid character' => 'nohponεξ'],
            ['invalid character +' => '+nohponex'],
            ['invalid character @' => '@nohponex'],
        ];
    }

    /**
     * @dataProvider validateSuccessProvider
     * @covers Phramework\Validate\Username::validate
     */
    public function testValidateSuccess($input)
    {
        $return = $this->object->validate($input);

        $this->assertInternalType('string', $return->value);
        $this->assertTrue($return->status);
    }

    /**
     * @dataProvider validateFailureProvider
     * @covers Phramework\Validate\Username::validate
     */
    public function testValidateFailure($input)
    {
        $return = $this->object->validate($input);

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\Username::getType
     */
    public function testGetType()
    {
        $this->assertEquals('username', $this->object->getType());
    }
}
