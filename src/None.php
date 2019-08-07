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

use EmptyIterator;
use UnicornFail\PhpOption\Exceptions\NoValueException;

// @todo Don't extend from Option, only implement the OptionInterface.
class None extends Option
{
    /**
     * A static instance, for performance reasons.
     *
     * @var static
     */
    protected static $instance;

    /**
     * {@inheritDoc}
     */
    public static function create($value = null, array $options = array())
    {
        if (static::$instance === null) {
            self::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * {@inheritDoc}
     */
    public function filter($callable)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function filterNot($callable)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function flatMap($callable)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function foldLeft($initialValue, $callable)
    {
        return $initialValue;
    }

    /**
     * {@inheritDoc}
     */
    public function foldRight($initialValue, $callable)
    {
        return $initialValue;
    }

    /**
     * {@inheritDoc}
     */
    public function forAll($callable)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        throw new NoValueException($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new EmptyIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrCall($callable)
    {
        return call_user_func($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrElse($default)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrThrow($exception)
    {
        throw $exception;
    }

    /**
     * {@inheritDoc}
     */
    public function isDefined()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function orElse(OptionInterface $else)
    {
        return $else;
    }

    /**
     * {@inheritDoc}
     */
    public function map($callable)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function select($value)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function reject($value)
    {
        return $this;
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
        // Intentionally do nothing.
    }
}
