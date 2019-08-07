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

use Closure;
use UnicornFail\PhpOption\Utility\Helper;

abstract class Option implements OptionInterface, CreateOptionInterface
{
    const NONE_VALUE = 'noneValue';
    const THROW_EXCEPTIONS = 'throwExceptions';

    /**
     * Creates an option from a callable.
     *
     * @param callable|mixed $callable
     *   A callable to execute.
     * @param array $options
     *   Optional. Additional options to associate with the value.
     *
     * @return LazyOption|None
     *   A lazy option that will evaluate the callback when necessary or None if callable isn't actually callable.
     */
    protected static function createFromCallable($callable, array $options = array())
    {
        /** @var static $static */
        $static = get_called_class();
        return new LazyOption(function () use ($callable, $options, $static) {
            return $static::create($callable(), $options);
        });
    }

    /**
     * Creates an option from an existing option.
     *
     * @param OptionInterface $option
     *   An option.
     * @param array $options
     *   Optional. Additional options to associate with the value.
     *
     * @return OptionInterface
     */
    protected static function createFromOption(OptionInterface $option, array $options = array())
    {
        if ($option instanceof SomeInterface) {
            $options = (array)array_replace_recursive($option->getOptions(), $options);
            $option->setOptions($options);
        }

        // Check to see if the none value has changed and matches the current value.
        $noneValue = static::getStaticOption($options, static::NONE_VALUE);
        $value = $option->getOrElse($noneValue);
        if ($value === $noneValue) {
            return None::create();
        }

        // If already the proper class, just return the same instance.
        if (get_class($option) === static::getClass()) {
            return $option;
        }

        // Send back to be re-created.
        return static::create($value, $options);
    }

    /**
     * @return string
     */
    protected static function getClass()
    {
        // Because Option is an abstract class, it cannot be instantiated itself.
        // In this case, default to "Some".
        $class = get_called_class();
        if (in_array($class, array('UnicornFail\\PhpOption\\Option', 'PhpOption\\Option'))) {
            $class = '\\UnicornFail\\PhpOption\\Some';
        }
        return ltrim($class, '\\');
    }

    /**
     * {@inheritDoc}
     */
    public static function create($value, array $options = array())
    {
        // Determine the none value from any passed options.
        $noneValue = static::getStaticOption($options, static::NONE_VALUE);

        // Immediately return if value is already the "none" value.
        if ($value === $noneValue) {
            return None::create();
        }

        // If value is already an instance, merge in any newly passed options and then immediately return.
        if ($value instanceof OptionInterface) {
            return static::createFromOption($value, $options);
        }

        // Handle callables.
        if (is_callable($value)) {
            return static::createFromCallable($value, $options);
        }

        // Finally, instantiate a new instance.
        $class = static::getClass();
        return new $class($value, $options);
    }

    /**
     * Retrieves the default options.
     *
     * @return array
     */
    public static function getDefaultOptions()
    {
        return array(
            self::NONE_VALUE => null,
            self::THROW_EXCEPTIONS => true,
        );
    }

    /**
     * Retrieves a static option from provided options.
     * @param array $options
     *   The provided options.
     * @param string $name
     *   The name of the option to retrieve.
     * @param mixed $default
     *   The default value to use if option is not currently set.
     *
     * @return mixed
     *   The option value or $default if not currently set.
     */
    protected static function getStaticOption(array $options, $name, $default = null)
    {
        $options = array_merge(static::getDefaultOptions(), $options);
        return array_key_exists($name, $options) ? $options[$name] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public static function find($iterable, $predicate, array $options = array())
    {
        if ($array = Helper::getArrayObject($iterable)) {
            $predicate = Helper::predicate($predicate, $options);
            foreach ($array as $key => $value) {
                if ($predicate($value)) {
                    return static::create($value, $options);
                }
            }
        }
        return None::create();
    }

    /**
     * {@inheritDoc}
     */
    public static function findKey($iterable, $predicate, array $options = array())
    {
        if ($array = Helper::getArrayObject($iterable)) {
            $predicate = Helper::predicate($predicate, $options);
            foreach ($array as $key => $value) {
                if ($predicate($value)) {
                    return static::create($key, $options);
                }
            }
        }
        return None::create();
    }

    /**
     * {@inheritDoc}
     */
    public static function pick($iterable, $predicate, array $options = array())
    {
        if ($array = Helper::getArrayObject($iterable)) {
            $predicate = Helper::predicate($predicate, $options);
            foreach ($array as $key => $value) {
                if ($predicate($key)) {
                    return static::create($value, $options);
                }
            }
        }
        return None::create();
    }

    /**
     * {@inheritDoc}
     */
    public static function pluck($iterable, $predicate, array $options = array())
    {
        if ($array = Helper::getArrayObject($iterable)) {
            $results = array();
            foreach ($array as $key => $value) {
                $value = static::pick($value, $predicate, $options)->getOrElse(null);
                if ($value !== null) {
                    $results[] = $value;
                }
            }
            if ($results) {
                return static::create($results, $options);
            }
        }
        return None::create();
    }

    /**********************************************
     *
     * DEPRECATED METHODS FROM ORIGINAL PHPOPTION.
     *
     **********************************************/

    /**
     * Option factory, which creates new option based on passed value.
     * If value is already an option, it simply returns
     * If value is a \Closure, LazyOption with passed callback created and returned. If Option returned from callback,
     * it returns directly (flatMap-like behaviour)
     * On other case value passed to Option::fromValue() method
     *
     * @param Option|Closure|mixed $value
     * @param null $noneValue used when $value is mixed or Closure, for None-check
     *
     * @return OptionInterface
     *
     * @deprecated since 1.6.0
     *   Use \UnicornFail\PhpOption\Option::create() or \UnicornFail\PhpOption\TypedOption::create() instead.
     *
     * @see \UnicornFail\PhpOption\Option::create()
     * @see \UnicornFail\PhpOption\TypedOption::create()
     */
    public static function ensure($value, $noneValue = null)
    {
        // Unfortunately, this cannot simply be mapped to ::create directly due
        // to the way it handled closures specifically.
        if ($value instanceof OptionInterface) {
            return $value;
        }
        if ($value instanceof Closure) {
            /** @var static $static */
            $static = get_called_class();
            return new LazyOption(function () use ($value, $noneValue, $static) {
                $return = $value();
                if ($return instanceof OptionInterface) {
                    return $return;
                }
                return $static::fromValue($return, $noneValue);
            });
        }
        return static::fromValue($value, $noneValue);
    }

    /**
     * Creates an option given a return value.
     *
     * This is intended for consuming existing APIs and allows you to easily
     * convert them to an option. By default, we treat ``null`` as the None case,
     * and everything else as Some.
     *
     * @param mixed $value The actual return value.
     * @param mixed $noneValue The value which should be considered "None"; null
     *                         by default.
     *
     * @return OptionInterface
     *
     * @deprecated since 1.6.0
     *   Use \UnicornFail\PhpOption\Option::create() or \UnicornFail\PhpOption\TypedOption::create::create() instead.
     *
     * @see \UnicornFail\PhpOption\Option::create()
     * @see \UnicornFail\PhpOption\TypedOption::create()
     */
    public static function fromValue($value, $noneValue = null)
    {
        if ($value === $noneValue) {
            return None::create();
        }
        return new Some($value, array(
            static::NONE_VALUE => $noneValue,
        ));
    }

    /**
     * Creates an option from an array's value.
     *
     * If the key does not exist in the array, the array is not actually an array, or the
     * array's value at the given key is null, None is returned.
     *
     * Otherwise, Some is returned wrapping the value at the given key.
     *
     * @param mixed $array a potential array value
     * @param string $key the key to check
     *
     * @return OptionInterface
     *
     * @deprecated since 1.6.0
     *   Use \UnicornFail\PhpOption\Option::pick() or \UnicornFail\PhpOption\TypedOption::pick() instead.
     *
     * @see \UnicornFail\PhpOption\Option::pick()
     * @see \UnicornFail\PhpOption\TypedOption::pick()
     */
    public static function fromArraysValue($array, $key)
    {
        return static::pick($array, $key);
    }

    /**
     * Creates a lazy-option with the given callback.
     *
     * This is also a helper constructor for lazy-consuming existing APIs where
     * the return value is not yet an option. By default, we treat ``null`` as
     * None case, and everything else as Some.
     *
     * @param callable $callback The callback to evaluate.
     * @param array $arguments
     * @param mixed $noneValue The value which should be considered "None"; null
     *                         by default.
     *
     * @return OptionInterface
     *
     * @deprecated since 1.6.0
     *   Use \UnicornFail\PhpOption\Option::ensure() or
     *   \UnicornFail\PhpOption\TypedOption::ensure() with a Closure instead.
     *
     * @see \UnicornFail\PhpOption\Option::ensure()
     * @see \UnicornFail\PhpOption\TypedOption::ensure()
     */
    public static function fromReturn($callback, array $arguments = array(), $noneValue = null)
    {
        /** @var static $static */
        $static = get_called_class();
        return static::create(function () use ($callback, $arguments, $noneValue, $static) {
            return $static::create(call_user_func_array($callback, $arguments), array(
                $static::NONE_VALUE => $noneValue,
            ));
        });
    }
}
