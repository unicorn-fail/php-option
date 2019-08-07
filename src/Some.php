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

use ArrayIterator;
use UnexpectedValueException;

class Some extends Option implements SomeInterface
{
    /**
     * An array of options.
     *
     * @var array
     */
    protected $options = array();

    /**
     * The original value (after it has been normalized).
     *
     * @var mixed
     */
    protected $original;

    /**
     * Flag indicating whether the option has been overridden.
     *
     * @var bool
     */
    protected $overridden;

    /**
     * The normalized value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Constructs a new Some instance.
     *
     * @param mixed $value
     *   A value object.
     * @param array $options
     *
     */
    public function __construct($value = null, array $options = array())
    {
        $this->setOptions($options);
        $this->set($value);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGet()
    {
        return $this->value;
    }

    /**
     * Performs the actual setting of the option's value.
     *
     * @param mixed $value
     *   The value to set.
     *
     * @return static
     */
    protected function doSet($value = null)
    {
        $this->value = $value;
        if ($this->original === null) {
            $this->original = $this->value;
        }
        $this->overridden = $this->value !== $this->original;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function filter($callable)
    {
        if (true === call_user_func($callable, $this->get())) {
            return $this;
        }
        return None::create();
    }

    /**
     * {@inheritDoc}
     */
    public function filterNot($callable)
    {
        if (false === call_user_func($callable, $this->get())) {
            return $this;
        }
        return None::create();
    }

    /**
     * {@inheritDoc}
     */
    public function flatMap($callable)
    {
        $rs = call_user_func($callable, $this->get());
        if (!$rs instanceof OptionInterface) {
            throw new UnexpectedValueException(sprintf(
                'Callables passed to ::flatMap() must return an Option. Maybe you should use map() instead?'
            ));
        }
        return $rs;
    }

    /**
     * {@inheritDoc}
     */
    public function foldLeft($initialValue, $callable)
    {
        return call_user_func($callable, $initialValue, $this->get());
    }

    /**
     * {@inheritDoc}
     */
    public function foldRight($initialValue, $callable)
    {
        return call_user_func($callable, $this->get(), $initialValue);
    }

    /**
     * {@inheritDoc}
     */
    public function forAll($callable)
    {
        call_user_func($callable, $this->get());
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->doGet();
    }

    /**
     * {@inheritDoc}
     */
    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        $options = $this->options;
        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator((array)$this->get());
    }

    /**
     * {@inheritDoc}
     */
    public function getOrCall($callable)
    {
        return $this->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrElse($default)
    {
        return $this->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrThrow($exception)
    {
        return $this->get();
    }

    /**
     * {@inheritDoc}
     */
    public function isDefined()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function map($callable)
    {
        return static::create(call_user_func($callable, $this->get()));
    }

    /**
     * {@inheritDoc}
     */
    public function orElse(OptionInterface $else)
    {
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function reject($value)
    {
        if ($this->get() === $value) {
            return None::create();
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function select($value)
    {
        if ($this->get() === $value) {
            return $this;
        }
        return None::create();
    }

    /**
     * Sets a new value for the option.
     *
     * @param mixed $value
     *   The value to set.
     *
     * @return static
     */
    public function set($value = null)
    {
        return $this->doSet($value);
    }

    /**
     * {@inheritDoc}
     */
    public function setOption($name, $value = null)
    {
        $options = $this->getOptions();
        $options[$name] = $value;
        return $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options = array())
    {
        $this->options = (array) array_replace_recursive(static::getDefaultOptions(), $options);
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
        call_user_func($callable, $this->get());
    }
}
