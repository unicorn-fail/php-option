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

use UnexpectedValueException;
use UnicornFail\PhpOption\Exceptions\InvalidCallableException;

// @todo Don't extend from Option, only implement the OptionInterface.
class LazyOption extends Option
{
    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var OptionInterface
     */
    protected $option;

    /**
     * Constructor.
     *
     * @param callable $callable
     * @param array $arguments
     */
    public function __construct($callable, array $arguments = array())
    {
        if (!is_callable($callable)) {
            throw new InvalidCallableException($callable);
        }
        $this->callable = $callable;
        $this->arguments = $arguments;
    }

    /**
     * Helper Constructor.
     *
     * @param callable $callback
     * @param array $arguments
     *
     * @return OptionInterface
     */
    public static function create($callback, array $arguments = array())
    {
        return new static($callback, $arguments);
    }

    /**
     * Retrieves the an option by invoking the callback that was stored.
     *
     * @return OptionInterface
     */
    protected function option()
    {
        if ($this->option === null) {
            /** @var OptionInterface $result */
            $result = call_user_func_array($this->callable, $this->arguments);
            if (!($result instanceof OptionInterface)) {
                throw new UnexpectedValueException(
                    'Lazy option callbacks must return an instance of \\UnicornFail\\PhpOption\\OptionInterface.'
                );
            }
            $this->option = $result;
        }
        return $this->option;
    }

    /**
     * {@inheritDoc}
     */
    public function filter($callable)
    {
        return $this->option()->filter($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function filterNot($callable)
    {
        return $this->option()->filterNot($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function flatMap($callable)
    {
        return $this->option()->flatMap($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function foldLeft($initialValue, $callable)
    {
        return $this->option()->foldLeft($initialValue, $callable);
    }

    /**
     * {@inheritDoc}
     */
    public function foldRight($initialValue, $callable)
    {
        return $this->option()->foldRight($initialValue, $callable);
    }

    /**
     * {@inheritDoc}
     */
    public function forAll($callable)
    {
        return $this->option()->forAll($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function isDefined()
    {
        return $this->option()->isDefined();
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return $this->option()->isEmpty();
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->option()->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->option()->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrCall($callable)
    {
        return $this->option()->getOrCall($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrElse($default)
    {
        return $this->option()->getOrElse($default);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrThrow($exception)
    {
        return $this->option()->getOrThrow($exception);
    }

    /**
     * {@inheritDoc}
     */
    public function orElse(OptionInterface $else)
    {
        return $this->option()->orElse($else);
    }

    /**
     * {@inheritDoc}
     */
    public function map($callable)
    {
        return $this->option()->map($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function select($value)
    {
        return $this->option()->select($value);
    }

    /**
     * {@inheritDoc}
     */
    public function reject($value)
    {
        return $this->option()->reject($value);
    }

    /**********************************************
     *
     * DEPRECATED METHODS FROM ORIGINAL PHPOPTION.
     *
     **********************************************/

    /**
     * @deprecated since 1.3.0
     *   Use ::forAll() instead.
     */
    public function ifDefined($callable)
    {
        $this->option()->ifDefined($callable);
    }
}
