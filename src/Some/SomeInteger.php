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

namespace UnicornFail\PhpOption\Some;

class SomeInteger extends SomeType
{
    /**
     * {@inheritDoc}
     */
    public static function getValidTypes()
    {
        return array('integer');
    }

    /**
     * {@inheritDoc}
     */
    public static function applies($value, array $options = array())
    {
        return is_integer($value) || (is_numeric($value) && strpos("$value", '.') === false);
    }

    /**
     * {@inheritDoc}
     */
    public static function transformValue($value, array $options = array())
    {
        return intval($value);
    }
}
