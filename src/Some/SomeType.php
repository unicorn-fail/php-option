<?php

/**
 * Copyright 2019 Mark Carver <mark.carver@me.com>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace UnicornFail\PhpOption\Some;

use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\OptionInterface;
use UnicornFail\PhpOption\Some;
use UnicornFail\PhpOption\TypedOptionInterface;

abstract class SomeType extends Some implements TypedOptionInterface
{
    /**
     * The determined normalized value type.
     *
     * @var string
     */
    protected $valueType;

    /**
     * {@inheritDoc}
     *
     * @return SomeType|OptionInterface
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

        // If it has reached here, then this is a standalone implementation.
        $class = get_called_class();

        // Because Option is an abstract class, it cannot be instantiated itself.
        // In this case, default to "Some".
        if ($class === get_class()) {
            $class = '\\UnicornFail\\PhpOption\\Some';
        }

        // Handle standalone typed options.
        if (is_subclass_of($class, '\\UnicornFail\\PhpOption\\TypedOptionInterface')) {
            /** @var static|TypedOptionInterface $class */
            if ($class::applies($value, $options)) {
                $value = $class::transformValue($value, $options);
            } else {
                $value = $noneValue;
            }
            if ($value === $noneValue) {
                return None::create();
            }
        }

        // Finally, instantiate a new instance.
        return new $class($value, $options);
    }

    protected function doSet($value = null)
    {
        // Check for valid types, if any.
        $valueType = strtolower(gettype($value));
        if (($validTypes = $this->getValidTypes()) && !in_array($valueType, $validTypes)) {
            if ($this->getOption(static::THROW_EXCEPTIONS)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid value type passed: %s. ' .
                    'Must be one of the following type(s): %s. ' .
                    'Use %s::create() instead.',
                    $valueType,
                    implode(', ', $validTypes),
                    get_called_class()
                ));
            }
            return $this;
        }
        $this->valueType = $valueType;
        return parent::doSet($value);
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        return $this->valueType;
    }
}
