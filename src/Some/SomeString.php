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

class SomeString extends SomeType
{
    /**
     * {@inheritDoc}
     */
    public static function getValidTypes()
    {
        return array('string');
    }

    /**
     * {@inheritDoc}
     */
    public static function applies($value, array $options = array())
    {
        return is_scalar($value) || (is_object($value) && method_exists($value, '__toString'));
    }

    /**
     * {@inheritDoc}
     */
    public static function transformValue($value, array $options = array())
    {
        // Cast to string in case the raw value is an object that implements the __toString method.
        return (string)$value;
    }
}
