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

namespace UnicornFail\PhpOption\Utility;

use ArrayAccess;
use ArrayObject;
use Closure;
use ReflectionFunction;
use Traversable;
use UnicornFail\PhpOption\Exceptions\InvalidCallableException;
use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Option;
use UnicornFail\PhpOption\OptionInterface;
use UnicornFail\PhpOption\Some;

class Helper
{
    /**
     * An associative array of function names where the values indicate if they exist or not.
     *
     * @var array
     */
    protected static $enabledFunctions = array();

    /**
     * Splits a string by a string (removing whitespace and quotes from each split value).
     *
     * @param string $string
     *   The value to split.
     * @param string $delimiter
     *   The delimiter used to split the string into multiple items.
     * @param int $limit
     *   Optional. If limit is set and positive, the returned array will contain a maximum
     *   of limit elements with the last element containing the rest of string.
     *
     * @return string[]
     *   The split string.
     */
    public static function explode($delimiter, $string, $limit = null)
    {
        // Well this is stupid...
        // @see https://www.php.net/manual/en/function.explode.php#112885
        $array = array_map('\\UnicornFail\\PhpOption\\Utility\\Helper::trim', (array)explode($delimiter, $string));
        if ($limit === null) {
            return $array;
        }
        return array_slice($array, $limit < 0 ? $limit : 0, abs($limit));
    }

    /**
     * Invokes a function, but first checks whether it is callable.
     *
     * @param callable $function
     *   The function to invoke.
     * @param array $args
     *   Arguments to pass along to the function.
     * @param bool $throwException
     *   Optional. Flag indicating whether or not any exceptions should be thrown.
     *   If not set, the value that was set when creating the instance will be used.
     * @param mixed $noneValue
     *   The value which should be considered "None"; null by default.
     *
     * @return OptionInterface
     *   An Option representation of the value returned by the function.
     */
    public static function invoke($function, array $args = array(), $throwException = true, $noneValue = null)
    {
        $name = static::toString($function);
        if (!isset(static::$enabledFunctions[$name]) || static::$enabledFunctions[$name] === null) {
            static::$enabledFunctions[$name] = is_callable($function);
        }
        if (!static::$enabledFunctions[$name]) {
            if ($throwException) {
                throw new InvalidCallableException($name);
            }
            return None::create();
        }
        $value = @call_user_func_array($function, $args);
        return Some::create($value, array(
            Option::NONE_VALUE => $noneValue,
            Option::THROW_EXCEPTIONS => $throwException
        ));
    }

    /**
     * Retrieves an ArrayObject instance for a given value.
     *
     * @param array|Traversable|ArrayAccess $value
     *   A traversable value.
     *
     * @return ArrayObject|null
     *   A new ArrayObject instance.
     */
    public static function getArrayObject($value = null)
    {
        if (is_array($value) || $value instanceof Traversable || $value instanceof ArrayAccess) {
            return new ArrayObject($value);
        }
    }

    /**
     * Ensures that a predicate is converted into a Closure to be used to determine a "truthy" value.
     *
     * @param mixed $value
     *   The value to be checked.
     * @param array $options
     *   - strict: (bool) Flag indicating whether to strict match the type of value the predicate is searching for.
     *     If you need 0 to match 0 or "0" or FALSE, set this option to FALSE. Defaults to TRUE.
     *
     * @return Closure|callable
     */
    public static function predicate($value, array $options = array())
    {
        // Immediately return if already callable.
        if (is_callable($value)) {
            return $value;
        }
        $haystack = (array)$value;
        $strict = isset($options['strict']) ? !!$options['strict'] : true;
        return function ($value) use ($haystack, $strict) {
            return in_array($value, $haystack, $strict);
        };
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Normalizes a value into a string.
     *
     * @param mixed $value
     *   A value to normalize.
     * @param bool $namedScalars
     *   Flag indicating whether certain scalars are returned as their name. For example,
     *   true, false and null would be "true", "false" and "null" respectively. If disabled,
     *   they would return "1", "" and "" respectively.
     *
     * @return string
     *   The normalized value.
     */
    public static function toString($value, $namedScalars = true)
    {
        // Immediately return if already a string.
        if (is_string($value)) {
            return $value;
        }

        // Scalars.
        if ($value === null || is_scalar($value)) {
            return (string)array_search($value, array(
                $namedScalars ? 'null' : '' => null,
                $namedScalars ? 'true' : '1' => true,
                $namedScalars ? 'false' : '' => false,
                (string)$value => $value,
            ), true);
        }

        // Objects and Closures.
        if (is_object($value)) {
            if ($value instanceof Closure) {
                $ref = new ReflectionFunction($value);
                return $ref->getName() . ':' . $ref->getStartLine() . ':' . $ref->getEndLine();
            }
            return get_class($value);
        }

        // Callable arrays.
        if (is_array($value) && is_callable($value)) {
            list ($class, $method) = $value;
            if (is_object($class)) {
                $class = get_class($class);
            }
            return ltrim($class, '\\') . "::$method";
        }

        // If all else fails, just hash a representation of the value.
        return hash('sha256', base64_encode(serialize($value)));
    }

    /**
     * Trims a string of whitespaces and quotes.
     *
     * @param string $string
     *   The string to trim.
     *
     * @return string
     *   The trimmed string.
     */
    public static function trim($string)
    {
        return trim($string, " \t\n\r\0\x0B\"'");
    }
}
