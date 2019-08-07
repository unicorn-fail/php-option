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

use ArrayAccess;
use Traversable;

interface CreateOptionInterface extends OptionInterface
{
    /**
     * Creates a new option.
     *
     * @param mixed|OptionInterface|callable $value
     *   A return value.
     * @param array $options
     *   Optional. Additional options to associate with the value.
     *
     * @return OptionInterface
     */
    public static function create($value, array $options = array());

    /**
     * Searches an iterable for the first value it matches by value.
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   An iterable to search.
     * @param mixed|callable $predicate
     *   A value to search for. The predicate is transformed into a closure that checks for a match.
     * @param array $options
     *   - strict: (bool) Flag indicating whether to strict match the type of value the predicate is searching for.
     *     If you need 0 to match 0 or "0" or FALSE, set this option to FALSE. Defaults to TRUE.
     *
     * @return OptionInterface
     *   Returns the first value that passes a truth test (determined by the predicate), or None if no value
     *   could be found that passes the test. Returns as soon as it finds an acceptable value and doesn't
     *   traverse the entire iterable.
     */
    public static function find($iterable, $predicate, array $options = array());

    /**
     * Searches an iterable for the first value it matches by key.
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   An iterable to search.
     * @param mixed|callable $predicate
     *   A key to search for. The predicate is transformed into a closure that checks for a match.
     * @param array $options
     *   - strict: (bool) Flag indicating whether to strict match the type of value the predicate is searching for.
     *     If you need 0 to match 0 or "0" or FALSE, set this option to FALSE. Defaults to TRUE.
     *
     * @return OptionInterface
     *   Returns the first key that passes a truth test (determined by the predicate), or None if no key
     *   could be found that passes the test. Returns as soon as it finds an acceptable value and doesn't
     *   traverse the entire iterable.
     */
    public static function findKey($iterable, $predicate, array $options = array());

    /**
     * Searches an iterable for the first value it matches by key.
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   An iterable to search.
     * @param mixed|callable $predicate
     *   A key to search for. The predicate is transformed into a closure that checks for a match.
     * @param array $options
     *   - strict: (bool) Flag indicating whether to strict match the type of value the predicate is searching for.
     *     If you need 0 to match 0 or "0" or FALSE, set this option to FALSE. Defaults to TRUE.
     *
     * @return OptionInterface
     *   Returns the value of the first key that passes a truth test (determined by the predicate), or None
     *   if no key could be found that passes the test. Returns as soon as it finds an acceptable value and doesn't
     *   traverse the entire iterable.
     */
    public static function pick($iterable, $predicate, array $options = array());

    /**
     * Searches an iterable of iterables for all values that match by key.
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   An iterable to search.
     * @param mixed|callable $predicate
     *   A key to search for. The predicate is transformed into a closure that checks for a match.
     * @param array $options
     *   - strict: (bool) Flag indicating whether to strict match the type of value the predicate is searching for.
     *     If you need 0 to match 0 or "0" or FALSE, set this option to FALSE. Defaults to TRUE.
     *
     * @return OptionInterface
     *   Returns all values where the key passes a truth test (determined by the predicate), or None
     *   if no key could be found that passes the test.
     */
    public static function pluck($iterable, $predicate, array $options = array());
}
