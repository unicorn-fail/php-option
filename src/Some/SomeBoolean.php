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

class SomeBoolean extends SomeType
{
    const FALSY = 'falsy';
    const TRUTHY = 'truthy';

    /**
     * {@inheritDoc}
     */
    public static function getDefaultOptions()
    {
        return array_merge(parent::getDefaultOptions(), array(
            static::FALSY => array('0', 'off', 'false', 'no'),
            static::TRUTHY => array('1', 'on', 'true', 'yes'),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getValidTypes()
    {
        return array('boolean');
    }

    /**
     * {@inheritDoc}
     */
    public static function applies($value, array $options = array())
    {
        return static::isTruthy($value, static::getStaticOption($options, static::TRUTHY))
            || static::isFalsy($value, static::getStaticOption($options, static::FALSY));
    }

    /**
     * Indicates whether a provided value is "falsy".
     *
     * @param mixed $value
     *   The value to test.
     * @param array $falsy
     *   Optional. An array of valid "falsy" values. If none are provided, the default set will be used.
     *
     * @return bool
     *   TRUE if "falsy", FALSE otherwise.
     */
    public static function isFalsy($value, array $falsy = array())
    {
        return $value === false || (
                is_string($value)
                && in_array(strtolower($value), array_map('strtolower', $falsy))
            );
    }

    /**
     * Indicates whether a provided value is "truthy".
     *
     * @param mixed $value
     *   The value to test.
     * @param array $truthy
     *   Optional. An array of valid "truthy" values. If none are provided, the default set will be used.
     *
     * @return bool
     *   TRUE if "truthy", FALSE otherwise.
     */
    public static function isTruthy($value, array $truthy = array())
    {
        return $value === true || (
                is_string($value)
                && in_array(strtolower($value), array_map('strtolower', $truthy))
            );
    }

    /**
     * {@inheritDoc}
     */
    public static function transformValue($value, array $options = array())
    {
        if (static::isTruthy($value, static::getStaticOption($options, static::TRUTHY))) {
            return true;
        }
        if (static::isFalsy($value, static::getStaticOption($options, static::FALSY))) {
            return false;
        }
        // Should never reach here, but just in case.
        return static::getStaticOption($options, static::NONE_VALUE); // @codeCoverageIgnore
    }
}
