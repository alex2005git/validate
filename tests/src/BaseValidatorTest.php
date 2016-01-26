<?php

namespace Phramework\Validate;

use Phramework\Exceptions\IncorrectParametersException;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015 - 2016-10-05 at 22:11:07.
 */
class BaseValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Boolean
     */
    protected $bool;
    protected $int;
    protected $str;
    protected $uint;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->bool = new BooleanValidator();
        $this->int = new IntegerValidator(-100000, 100000);
        $this->str = new StringValidator();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Phramework\Validate\BaseValidator::parseStatic
     */
    public function testParseStatic()
    {
        $this->assertSame(
            5,
            IntegerValidator::parseStatic('5'),
            'Expect to convert 5 to integer'
        );

        $this->assertSame(
            5.5,
            NumberValidator::parseStatic('5.5')
        );

        $o = ObjectValidator::parseStatic(['ok' => true]);

        $this->assertInternalType('object', $o);
        $this->assertObjectHasAttribute('ok', $o);
        $this->assertSame(true, $o->ok);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromJSON
     */
    public function testCreateFromJSON()
    {
        $json = '{
            "type": "integer",
            "minimum" : -1000,
            "maximum" : 1000
        }';

        $validationObject = IntegerValidator::createFromJSON($json);

        $this->assertInstanceOf(BaseValidator::class, $validationObject);

        $this->assertSame(
            -1000,
            $validationObject->minimum
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromJSON
     */
    public function testCreateFromJSON2()
    {
        $json = '{
            "type": "unsignedinteger",
            "minimum" : -1000,
            "maximum" : 1000
        }';

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(BaseValidator::class, $validationObject);
        $this->assertInstanceOf(UnsignedIntegerValidator::class, $validationObject);

        $this->assertSame(
            -1000,
            $validationObject->minimum
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromJSON
     */
    public function testCreateFromJSON3()
    {
        $json = '
        {
          "type": "object",
          "properties": {
            "data": {
              "type": "object",
              "properties": {
                "type": {
                  "type": "enum",
                  "enum": ["user"]
                },
                "order": {
                  "type": "unsignedinteger",
                  "default" : 0
                }
              },
              "required": ["type"]
            }
          }
        }';

        $validationObject = ObjectValidator::createFromJSON($json);

        $this->assertInstanceOf(ObjectValidator::class, $validationObject);
        $this->assertInternalType(
            'object',
            $validationObject->properties
        );
        $this->assertInstanceOf(
            ObjectValidator::class,
            $validationObject->properties->data
        );

        $data = $validationObject->properties->data;

        $this->assertInstanceOf(
            EnumValidator::class,
            $data->properties->type
        );

        $this->assertInstanceOf(
            UnsignedIntegerValidator::class,
            $data->properties->order
        );
        $this->assertInternalType(
            'array',
            $data->properties->type->enum
        );

        $this->assertSame(
            0,
            $data->properties->order->default
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromJSON
     */
    public function testCreateFromJSONNot()
    {
        $json = '{
            "type": "integer",
            "minimum": -1000,
            "maximum": 1000,
            "not": {
                "type" : "integer",
                "enum" : [1, 2]
            }
        }';

        $validator = BaseValidator::createFromJSON($json);

        $this->assertSame(5, $validator->parse(5));

        $return = $validator->validate(2);

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromJSON
     * @expectedException Exception
     */
    public function testCreateFromJSONFailure()
    {
        $json = '{
            "type": "xyz",
            "minimum" : -1000,
            "maximum" : 1000
        }';

        $validationObject = IntegerValidator::createFromJSON($json);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromJSON
     * @expectedException Exception
     */
    public function testCreateFromJSONFailure2()
    {
        //Add an unexpected comma at the end of JSON string
        $json = '{
            "type": "interger",
            "minimum" : -1000,
            "maximum" : 1000,
        }';

        $validationObject = IntegerValidator::createFromJSON($json);
    }



    /**
     * @covers Phramework\Validate\BaseValidator::parse
     */
    public function testParseSuccess()
    {
        $validationObject = new ObjectValidator(
            [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    (object)[ //properties
                        'valid' => new BooleanValidator(),
                        'number' => new NumberValidator(0, 100),
                        'not_required' => (new NumberValidator(0, 100))->setDefault(5.5),
                    ],
                    ['valid'] //required
                )
            ],
            ['weight'] //required
        );

        $input = (object)[
            'weight' => '5',
            'obj' => (object)[
                'valid' => 'true',
                'number' => 10.2,
            ]
        ];

        $record = $validationObject->parse($input);
        $this->assertInternalType('object', $record);
        $this->assertInternalType('object', $record->obj);
        $this->assertInternalType('float', $record->obj->not_required);
        $this->assertEquals(5, $record->weight);
        $this->assertTrue($record->obj->valid);
        $this->assertEquals(5.5, $record->obj->not_required);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::parse
     */
    public function testParseSuccess2()
    {
        $input = '5';

        $validationModel = new IntegerValidator(0, 6);

        $cleanInput = $validationModel->parse($input);

        $this->assertInternalType('integer', $cleanInput);
        $this->assertEquals(5, $cleanInput);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::parse
     * @expectedException Exception
     * @todo \Phramework\Exceptions\MissingParametersException
     */
    public function testParseFailure()
    {
        $input = [
            'weight' => '5',
            'obj' => [
                //'valid' => 'true',
                'number' => 10.2,
            ]
        ];

        $validationObject = new ObjectValidator(
            [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    [ //properties
                        'valid' => new BooleanValidator(),
                        'number' => new NumberValidator(0, 100),
                        'not_required' => (new NumberValidator(0, 100))->setDefault(5.5),
                    ],
                    ['valid'] //required
                )
            ],
            ['weight'] //required
        );

        $record = $validationObject->parse($input);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::parse
     * @expectedException Exception
     * @todo \Phramework\Exceptions\IncorrectParametersException
     */
    public function testParseFailure2()
    {
        $input = [
            'weight' => '555', //out of range
            'obj' => [
                'valid' => 'ΝΟΤ_VALID',
                'number' => 10.2
            ]
        ];

        $validationObject = new ObjectValidator(
            [ //properties
                'weight' => new IntegerValidator(-10, 10, true),
                'obj' => new ObjectValidator(
                    [ //properties
                        'valid' => new BooleanValidator(),
                        'number' => new NumberValidator(0, 100),
                        'not_required' => (new NumberValidator(0, 100))
                            ->setDefault(5),
                    ],
                    ['valid'] //required
                )
            ],
            ['weight'] //required
        );

        $record = $validationObject->parse($input);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::__construct
     */
    public function testConstruct()
    {
        $validator = new ArrayValidator(
            1,
            3
        );
    }
    /**
     * @covers Phramework\Validate\BaseValidator::createFromObject
     */
    public function testCreateFromObjectTypeless()
    {
        $validator = IntegerValidator::createFromObject((object)[]);

        $this->assertInstanceOf(IntegerValidator::class, $validator);
    }

    /**
     * Validate against common enum keyword
     * @covers Phramework\Validate\BaseValidator::validateCommon
     */
    public function testValidateCommon()
    {
        (new IntegerValidator())->parse(5);

        $validator = (new IntegerValidator())
            ->setNot(new EnumValidator([5]));

        $return = $validator->validate(5);
        $this->assertFalse(
            $return->status
        );

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
     * Validate against common enum keyword
     * @covers Phramework\Validate\BaseValidator::validateEnum
     */
    public function testValidateEnum()
    {
        $validator = (new IntegerValidator(0, 10));

        $validator->enum = [1, 3, 5];
        $validator->validateType = true;

        $return = $validator->validate(1);

        $this->assertTrue(
            $return->status,
            'Expect true since 1 is in enum'
        );

        $return = $validator->validate("1");

        $this->assertFalse(
            $return->status,
            'Expect false since "1" is not correct type'
        );

        $return = $validator->validate(2);
        $this->assertFalse(
            $return->status,
            'Expect false since "2" is not in enum'
        );

        $validator = (new EnumValidator([111, 3, 5]));

        $parsed = $validator->parse('111');

        $this->assertInternalType('integer', $parsed);
        $this->assertSame(111, $parsed);
    }

    /**
     * Validate against common enum keyword,
     * expect exception since objects and arrays are not yet supported for enum keyword
     * @expectedException Exception
     * @covers Phramework\Validate\BaseValidator::validateEnum
     */
    public function testValidateEnumException2()
    {
        $validator = (new IntegerValidator(0, 10));

        $validator->enum = [[1], new \stdClass(), 5];

        $validator->validate(2);
    }

    /**
     * Validate against common enum keyword
     * @covers Phramework\Validate\BaseValidator::validateNot
     */
    public function testValidateNot()
    {
        $validator = new StringValidator();

        $validator->not = new IntegerValidator();

        $validator->parse('asdf');

        $this->assertSame('asdf', $validator->parse('asdf'));

        $return = $validator->validate('5');

        $this->assertFalse($return->status);

        $parameters = $return->errorObject->getParameters();

        $this->assertSame('not', $parameters[0]['failure']);

        $validator = new StringValidator();

        $validator->not = new EnumValidator(['a', 'aa']);

        $this->assertSame('asdf', $validator->parse('asdf'));

        $return = $validator->validate('aa');

        $this->assertFalse($return->status);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::parse
     * @expectedException Exception
     */
    public function testParseFailure3()
    {
        $input = '87';

        $validationModel = new IntegerValidator(0, 6);

        $cleanInput = $validationModel->parse($input);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::__get
     */
    public function testGet()
    {
        $validator = new IntegerValidator();

        $validator->setDefault(0);

        $this->assertEquals('integer', $validator->type);

        $this->assertEquals(0, $validator->__get('default'));
    }

    /**
     * @covers Phramework\Validate\BaseValidator::__get
     * @expectedException Exception
     */
    public function testGet2()
    {
        $validator = new IntegerValidator();
        $validator->IM_SURE_THIS_CANT_BE_FOUND;
    }

    /**
     * @covers Phramework\Validate\BaseValidator::__set
     * @expectedException Exception
     */
    public function testSetFailure()
    {
        $validator = new IntegerValidator();
        $validator->IM_SURE_THIS_CANT_BE_FOUND = 'value';
    }

    /**
     * @covers Phramework\Validate\BaseValidator::getType
     */
    public function testGetType()
    {
        $validator = new IntegerValidator();
        $this->assertEquals('integer', $validator->getType());
    }

    /**
     * @covers Phramework\Validate\BaseValidator::getTypeAttributes
     */
    public function testGetTypeAttributes()
    {
        $validator = new IntegerValidator();
        $this->assertInternalType('array', $validator->getTypeAttributes());

        foreach ($validator->getTypeAttributes() as $attribute) {
            $this->assertInternalType('string', $attribute);
        }
    }

    /**
     * @covers Phramework\Validate\BaseValidator::__set
     */
    public function testSetSuccess()
    {
        $validator = new IntegerValidator();
        $validator->title = 'my title';

        $this->assertSame(
            $validator->title,
            'my title'
        );

        $returnValue = $validator->__set('title', 'my title');

        $this->assertInstanceOf(
            IntegerValidator::class,
            $returnValue
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::setTitle
     */
    public function testSetTitle()
    {
        $validator = new IntegerValidator();
        $validator->setTitle('my title');
        $this->assertSame(
            $validator->title,
            'my title'
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::setDescription
     */
    public function testSetDescription()
    {
        $validator = new IntegerValidator();
        $validator->setDescription('my description');
        $this->assertSame(
            $validator->description,
            'my description'
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::setDefault
     */
    public function testSetDefault()
    {
        $validator = new IntegerValidator();
        $validator->setDefault(222);

        $this->assertSame(
            $validator->default,
            222
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::setNot
     */
    public function testSetNot()
    {
        $validator = new IntegerValidator();

        $validator->setNot(new EnumValidator([0, 2]));
    }

    /**
     * @covers Phramework\Validate\BaseValidator::setNot
     * @expectedException Exception
     */
    public function testSetNotFailure()
    {
        $validator = new IntegerValidator();

        $validator->setNot([0, 1]);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::setEnum
     */
    public function testSetEnum()
    {
        $validator = new IntegerValidator();

        $enum = [1, 2, 3];

        $returnValue = $validator->setEnum($enum);

        $this->assertSame(
            $validator->enum,
            $enum
        );

        $this->assertInstanceOf(
            IntegerValidator::class,
            $returnValue
        );

        $return = $validator->validate(4);

        $this->assertFalse(
            $return->status,
            'Expect true since "4" is not in enum array'
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromArray
     */
    public function testCreateFromArray()
    {
        $schema = [
            'type' => 'integer',
            'minimum' =>  1,
            'maximum' =>  2,
        ];

        $validator = BaseValidator::createFromArray($schema);

        $this->assertInstanceOf(IntegerValidator::class, $validator);

        $this->assertSame(1, $validator->minimum);
        $this->assertSame(2, $validator->maximum);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromObject
     */
    public function testCreateFromObject()
    {
        $schema = (object)[
            'type' => 'integer',
            'minimum' =>  1,
            'maximum' =>  2,
        ];

        $validator = BaseValidator::createFromObject($schema);

        $this->assertInstanceOf(IntegerValidator::class, $validator);

        $this->assertSame(1, $validator->minimum);
        $this->assertSame(2, $validator->maximum);

        $schema = (object)[
            'type' => 'object',
            'properties' => (object)[
                'code' => (object)[
                    'type' => 'integer'
                ]
            ]
        ];

        $validator = BaseValidator::createFromObject($schema);

        $this->assertInstanceOf(ObjectValidator::class, $validator);

        $validator->parse((object)['code' => 10]);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::createFromObject
     * @expectedException Exception
     */
    public function testCreateFromObjectFailure()
    {
        $object = (object)[
            'type' => 'x-not-found'
        ];

        $validator = BaseValidator::createFromObject($object);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::toObject
     */
    public function testToObject()
    {
        $return = $this->int->toObject();

        $this->assertInternalType('object', $return);

        $this->assertObjectHasAttribute('type', $return);
        $this->assertObjectHasAttribute('minimum', $return);
        $this->assertObjectHasAttribute('maximum', $return);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::toObject
     */
    public function testToObjec2()
    {
        $return = (new ObjectValidator(
            [
                'int' => new IntegerValidator()
            ],
            ['int']
        ))->toObject();

        $this->assertInternalType('object', $return);

        $this->assertObjectHasAttribute('type', $return);
        $this->assertObjectHasAttribute('properties', $return);
        $this->assertObjectHasAttribute('required', $return);

        $this->assertInternalType('object', $return->properties);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::toArray
     */
    public function testToArray()
    {
        $return = $this->int->toArray();

        $this->assertInternalType('array', $return);

        $this->assertArrayHasKey('type', $return);
        $this->assertArrayHasKey('minimum', $return);
        $this->assertArrayHasKey('maximum', $return);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::toArray
     */
    public function testToArray2()
    {
        $return = (new ObjectValidator(
            [
                'int' => new IntegerValidator()
            ],
            ['int']
        ))->toArray();

        $this->assertInternalType('array', $return);

        $this->assertArrayHasKey('type', $return);
        $this->assertArrayHasKey('properties', $return);
        $this->assertArrayHasKey('required', $return);

        $this->assertInternalType('array', $return['properties']);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::toJSON
     */
    public function testToJSON()
    {
        $return = $this->int->toJSON();

        $this->assertInternalType('string', $return);

        $jsonObject = json_decode($return);

        //assert no errors
        $this->assertSame(JSON_ERROR_NONE, json_last_error());

        $this->assertObjectHasAttribute('type', $jsonObject);
        $this->assertObjectHasAttribute('minimum', $jsonObject);
        $this->assertObjectHasAttribute('maximum', $jsonObject);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::registerValidator
     */
    public function testRegisterValidator()
    {
        BaseValidator::registerValidator(
            \Phramework\Validate\APP\AddressValidator::getType(),
            \Phramework\Validate\APP\AddressValidator::class
        );

        $json = sprintf(
            '{
                "type": "%s",
                "minLength" : 4,
                "maxLength" : 20
            }',
            \Phramework\Validate\APP\AddressValidator::getType()
        );

        $validationObject = BaseValidator::createFromJSON($json);

        $this->assertInstanceOf(
            \Phramework\Validate\APP\AddressValidator::class,
            $validationObject
        );

        $this->assertSame(
            $validationObject->minLength,
            4
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::registerValidator
     * @expectedException Exception
     */
    public function testRegisterValidatorFailure()
    {
        BaseValidator::registerValidator(
            \Phramework\Validate\APP\AddressValidator::getType(),
            \stdClass::class
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::registerValidator
     * @expectedException Exception
     */
    public function testRegisterValidatorFailure2()
    {
        BaseValidator::registerValidator(
            5,
            \Phramework\Validate\APP\AddressValidator::class
        );
    }

    /**
     * @covers Phramework\Validate\BaseValidator::registerValidator
     * @expectedException Exception
     */
    public function testRegisterValidatorFailure3()
    {
        BaseValidator::registerValidator(
            \Phramework\Validate\APP\AddressValidator::getType(),
            34
        );
    }
    /**
     * @covers Phramework\Validate\BaseValidator::runValidateCallback
     */
    public function testRunValidateCallback()
    {
        (new IntegerValidator())->parse(5);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::setValidateCallback
     */
    public function testSetValidateCallback()
    {
        $value = 5;

        $validator = (new IntegerValidator())
            ->setValidateCallback(
                /**
                 * @param ValidateResult $validateResult
                 * @param BaseValidator $validator
                 * @return ValidateResult
                 */
                function ($validateResult, $validator) {
                    $validateResult->value = $validateResult->value + 1;

                return $validateResult;
            });

        $this->assertInstanceOf(IntegerValidator::class, $validator);

        $parsed = $validator->parse($value);

        $this->assertInternalType('integer', $parsed);
        $this->assertSame($value + 1, $parsed);
    }

    /**
     * @covers Phramework\Validate\BaseValidator::runValidateCallback
     * @expectedException Exception
     */
    public function testSetValidateFailure()
    {
        $validator = (new IntegerValidator())
            ->setValidateCallback(function ($validateResult, $validator) {
                $validateResult->status = false;
                $validateResult->errorObject = new IncorrectParametersException([
                    [
                        'type' => 'integer',
                        'failure' => 'callback'
                    ]
                ]);

                return $validateResult;
            });

        $validator->parse(5);
    }
}
