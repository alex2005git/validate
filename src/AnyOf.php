<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Validate;

use \Phramework\Validate\ValidateResult;
use \Phramework\Exceptions\IncorrectParametersException;

/**
 * @property array anyOf
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.4.0
 */
class AnyOf extends \Phramework\Validate\BaseValidator
{
    /**
     * Overwrite base class type
     * @var null
     */
    protected static $type = null;

    protected static $typeAttributes = [
        'anyOf'
    ];

    public function __construct(
        array $anyOf
    ) {
        parent::__construct();

        foreach ($anyOf as $validator) {
            if (!($validator instanceof \Phramework\Validate\BaseValidator)) {
                throw new \Exception(
                    'Items of anyOf parameter MUST be instances of Phramework\Validate\BaseValidator'
                );
            }
        }

        $this->anyOf = $anyOf;
    }

    /**
     * Validate value
     * @see \Phramework\Validate\ValidateResult for ValidateResult object
     * @param  mixed $value Value to validate
     * @return ValidateResult
     * @uses https://secure.php.net/manual/en/function.is-string.php
     * @uses filter_var with FILTER_VALIDATE_REGEXP for pattern
     */
    public function validate($value)
    {
        $return = new ValidateResult($value, false);
        //validator ->
        //return    ->
        $successValidated = [];

        foreach ($this->anyOf as $validator) {
            $validatorReturn = $validator->validate($value);

            if ($validatorReturn->status) {
                //push to successValidated list
                $successValidated[] = (object)[
                    'validator' => $validator,
                    'return'    => $validatorReturn
                ];
            }
        }

        if (count($successValidated) > 0) {
            //Use first in list
            $return = $successValidated[0]->return;
        }

        unset($successValidated);

        return $this->validateCommon($value, $return);
    }
}
