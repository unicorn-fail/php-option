<?php

/**
 * Copyright 2019 Mark Carver <mark.carver@me.com>
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
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

use Exception;
use IteratorAggregate;
use Throwable;
use UnicornFail\PhpOption\Exceptions\NoValueException;

interface OptionInterface extends IteratorAggregate
{
    /**
     * Applies the callable to the value of the option if it is non-empty, and
     * returns the return value of the callable directly.
     *
     * In contrast to ``map``, the return value of the callable is expected to
     * be an OptionInterface itself; it is not automatically wrapped in Some().
     *
     * @param callable $callable
     *   Must return an OptionInterface based object.
     *
     * @throws \UnexpectedValueException
     *   When the return value of the $callable does not return an OptionInterface based object.
     *
     * @return OptionInterface
     */
    public function flatMap($callable);

    /**
     * If the option is empty, it is returned immediately without applying the callable.
     *
     * If the option is non-empty, the callable is applied, and if it returns true,
     * the option itself is returned; otherwise, None is returned.
     *
     * @param callable $callable
     *
     * @return OptionInterface
     */
    public function filter($callable);

    /**
     * If the option is empty, it is returned immediately without applying the callable.
     *
     * If the option is non-empty, the callable is applied, and if it returns false,
     * the option itself is returned; otherwise, None is returned.
     *
     * @param callable $callable
     *
     * @return OptionInterface
     */
    public function filterNot($callable);

    /**
     * Binary operator for the initial value and the option's value.
     *
     * If empty, the initial value is returned.
     * If non-empty, the callable receives the initial value and the option's value as arguments
     *
     * ```php
     *
     *     $some = new Some(5);
     *     $none = None::create();
     *     $result = $some->foldLeft(1, function($a, $b) { return $a + $b; }); // int(6)
     *     $result = $none->foldLeft(1, function($a, $b) { return $a + $b; }); // int(1)
     *
     *     // This can be used instead of something like the following:
     *     $option = Option::fromValue($integerOrNull);
     *     $result = 1;
     *     if ( ! $option->isEmpty()) {
     *         $result += $option->get();
     *     }
     * ```
     *
     * @param mixed $initialValue
     * @param callable $callable function(initialValue, callable): result
     *
     * @return mixed
     */
    public function foldLeft($initialValue, $callable);

    /**
     * foldLeft() but with reversed arguments for the callable.
     *
     * @param mixed $initialValue
     * @param callable $callable function(callable, initialValue): result
     *
     * @return mixed
     */
    public function foldRight($initialValue, $callable);

    /**
     * This is similar to map() except that the return value of the callable has no meaning.
     *
     * The passed callable is simply executed if the option is non-empty, and ignored if the
     * option is empty. This method is preferred for callables with side-effects, while map()
     * is intended for callables without side-effects.
     *
     * @param callable $callable
     *
     * @return OptionInterface
     */
    public function forAll($callable);

    /**
     * Returns the value if available, or throws an exception otherwise.
     *
     * @return mixed
     *
     * @throws NoValueException
     *   When no value is available.
     */
    public function get();

    /**
     * Returns the value if available, or the default value if not.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOrElse($default);

    /**
     * Returns the value if available, or the results of the callable.
     *
     * This is preferable over ``getOrElse`` if the computation of the default
     * value is expensive.
     *
     * @param callable $callable
     *
     * @return mixed
     */
    public function getOrCall($callable);

    /**
     * Returns the value if available, or throws the passed exception.
     *
     * @param Exception|Throwable $exception
     *
     * @return mixed
     */
    public function getOrThrow($exception);

    /**
     * Returns true if a value is available, false otherwise.
     *
     * @return boolean
     */
    public function isDefined();

    /**
     * Returns true if no value is available, false otherwise.
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * Returns this option if non-empty, or the passed option otherwise.
     *
     * This can be used to try multiple alternatives, and is especially useful
     * with lazy evaluating options:
     *
     * ```php
     *     $repo->findSomething()
     *         ->orElse(new LazyOption(array($repo, 'findSomethingElse')))
     *         ->orElse(new LazyOption(array($repo, 'createSomething')));
     * ```
     *
     * @param OptionInterface $else
     *
     * @return OptionInterface
     */
    public function orElse(OptionInterface $else);

    /**
     * Applies the callable to the value of the option if it is non-empty,
     * and returns the return value of the callable wrapped in Some().
     *
     * If the option is empty, then the callable is not applied.
     *
     * ```php
     *     (new Some("foo"))->map('strtoupper')->get(); // "FOO"
     * ```
     *
     * @param callable $callable
     *
     * @return OptionInterface
     */
    public function map($callable);

    /**
     * If the option is empty, it is returned immediately.
     *
     * If the option is non-empty, and its value does not equal the passed value
     * (via a shallow comparison ===), then None is returned. Otherwise, the
     * Option is returned.
     *
     * In other words, this will filter all but the passed value.
     *
     * @param mixed $value
     *
     * @return OptionInterface
     */
    public function select($value);

    /**
     * If the option is empty, it is returned immediately.
     *
     * If the option is non-empty, and its value does equal the passed value (via
     * a shallow comparison ===), then None is returned; otherwise, the Option is
     * returned.
     *
     * In other words, this will let all values through except the passed value.
     *
     * @param mixed $value
     *
     * @return OptionInterface
     */
    public function reject($value);

    /**********************************************
     *
     * DEPRECATED METHODS FROM ORIGINAL PHPOPTION.
     *
     **********************************************/

    /**
     * This is similar to ::map() except that the return value has no meaning.
     *
     * The passed callable is simply executed if the option is non-empty, and
     * ignored if the option is empty.
     *
     * In all cases, the return value of the callable is discarded.
     *
     * ```php
     *     $comment->getMaybeFile()->ifDefined(function($file) {
     *         // Do something with $file here.
     *     });
     * ```
     *
     * If you're looking for something like ``ifEmpty``, you can use ``getOrCall``
     * and ``getOrElse`` in these cases.
     *
     * @param callable $callable
     *
     * @return void
     *
     * @deprecated since 1.3.0
     *   Use ::forAll() instead.
     */
    public function ifDefined($callable);
}
