<?php

/**
 * Copyright 2019 Mark Carver <mark.carver@me.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace UnicornFail\PhpOption;

interface TypedOptionInterface extends OptionInterface
{
    /**
     * Indicates whether the type applies.
     *
     * @param mixed $value
     *   The raw value to check.
     * @param array $options
     *   Optional. Additional options to associate with the value.
     *
     * @return bool
     *   TRUE if the value type is valid, FALSE otherwise.
     */
    public static function applies($value, array $options = array());

    /**
     * Retrieves the expected PHP type(s).
     *
     * @return string[]
     */
    public static function getValidTypes();

    /**
     * Transforms a raw value into the appropriate type, if possible.
     *
     * @param mixed $value
     *   The raw value to transform.
     * @param array $options
     *   Optional. Additional options to associate with the value.
     *
     * @return mixed
     *   The transformed value.
     */
    public static function transformValue($value, array $options = array());
}
