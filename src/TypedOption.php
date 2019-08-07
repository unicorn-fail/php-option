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

use UnexpectedValueException;
use UnicornFail\PhpOption\Exceptions\MissingTypedOptionsException;

abstract class TypedOption extends Option implements TypedOptionInterface
{
    /**
     * An array of class names that implement the OptionTypeTransformInterface that will be used to transform values.
     *
     * @var string[]
     */
    protected static $typedOptions = array(
        '\\UnicornFail\\PhpOption\\Some\\SomeFloat',
        '\\UnicornFail\\PhpOption\\Some\\SomeInteger',
        '\\UnicornFail\\PhpOption\\Some\\SomeBoolean',
        '\\UnicornFail\\PhpOption\\Some\\SomeArray',
        '\\UnicornFail\\PhpOption\\Some\\SomeString',
    );

    /**
     * {@inheritDoc}
     *
     * @return TypedOption
     */
    public static function create($value, array $options = array())
    {
        // Immediately return if there are no Some types registered.
        if (!static::$typedOptions) {
            throw new MissingTypedOptionsException(get_called_class());
        }

        $option = None::create();
        foreach (static::$typedOptions as $class) {
            /** @var static|TypedOptionInterface $class */
            if (
                !is_string($class)
                || !is_subclass_of($class, '\\UnicornFail\\PhpOption\\TypedOptionInterface')
            ) {
                throw new UnexpectedValueException(sprintf(
                    'Provided class is not valid: %s. Must be an instance of %s.',
                    $class,
                    '\\UnicornFail\\PhpOption\\TypedOptionInterface'
                ));
            }
            if ($class::applies($value, $options)) {
                $option = $option->orElse(LazyOption::create(function () use ($class, $value, $options) {
                    return $class::create($value, $options);
                }));
            }
        }
        return $option->orElse(None::create());
    }
}
