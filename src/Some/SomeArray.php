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

use ArrayAccess;
use UnicornFail\PhpOption\Utility\Helper;

class SomeArray extends SomeType
{
    const KEY_DELIMITER = 'keyDelimiter';
    const LIST_DELIMITER = 'listDelimiter';

    /**
     * {@inheritDoc}
     */
    public static function getValidTypes()
    {
        return array('array');
    }
    /**
     * {@inheritDoc}
     */
    public static function getDefaultOptions()
    {
        return array_merge(parent::getDefaultOptions(), array(
            static::KEY_DELIMITER => '=',
            static::LIST_DELIMITER => ',',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function applies($value, array $options = array())
    {
        return is_array($value)
            || $value instanceof ArrayAccess
            || (
                is_string($value)
                && ($listDelimiter = static::getStaticOption($options, static::LIST_DELIMITER))
                && strpos($value, $listDelimiter) !== false
            );
    }

    /**
     * {@inheritDoc}
     */
    public static function transformValue($value, array $options = array())
    {
        // Immediately return if already an array, typecasting to deal with objects implementing ArrayAccess.
        if (is_array($value) || $value instanceof ArrayAccess) {
            return (array)$value;
        }

        // Otherwise, it is possibly a delimited string.
        $listDelimiter = static::getStaticOption($options, static::LIST_DELIMITER);
        $keyDelimiter = static::getStaticOption($options, static::KEY_DELIMITER);

        // Convert the delimited string into an INI file that can be parsed.
        // This will allow for deep nesting of values.
        $index = -1;
        $items = Helper::explode($listDelimiter, $value);
        foreach ($items as &$item) {
            // Prepend item with an index key if it's not associative.
            $keyPos = strpos($item, $keyDelimiter);
            if ($keyDelimiter === null || $keyPos === false) {
                $item = ++$index . $keyDelimiter . substr($item, $keyPos !== false ? $keyPos : 0);
            }

            // Normalize to a proper INI key=value pair.
            list($iniKey, $iniValue) = Helper::explode($keyDelimiter, $item, 2);
            $item = "$iniKey=\"$iniValue\"";
        }
        $ini = implode("\n", $items);

        return Helper::invoke(
            'parse_ini_string',
            array($ini),
            self::getStaticOption($options, static::THROW_EXCEPTIONS),
            false
        )->getOrElse(self::getStaticOption($options, static::NONE_VALUE));
    }
}
