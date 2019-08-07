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

namespace UnicornFail\PhpOption\Tests\Framework;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class OptionTestCase extends TestCase
{
    /**
     * Helper method to set the expected exception of a test.
     *
     * Note: this is primarily needed to deal with the different phpunit version.
     *
     * @param string $exception
     *   The expected exception class.
     * @param string $message
     *   Optional. The expected exception message.
     */
    protected function assertException($exception, $message = null)
    {
        if (class_exists('\PHPUnit_Runner_Version')) {
            $this->setExpectedException($exception, $message);
        } else {
            $this->expectException($exception);
            if ($message !== null) {
                $this->expectExceptionMessage($message);
            }
        }
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Helper method for setting the static $functions variable on the PhpIni object.
     *
     * @param array $value
     *   An array of functions to set.
     *
     * @return array
     *   The previous state of the variable.
     */
    protected function setEnabledFunctions(array $value)
    {
        $ref = new ReflectionProperty('\\UnicornFail\\PhpOption\\Utility\\Helper', 'enabledFunctions');
        $ref->setAccessible(true);
        $original = $ref->getValue(null);
        $ref->setValue(null, $value);
        return $original;
    }

    /**
     * Executes a callback by temporarily faking a function being "disabled".
     *
     * @param string|string[] $function
     *   The function to disable.
     * @param callable $callback
     *   The callback to execute.
     *
     * @return mixed
     *   The return value of the callback.
     */
    protected function disableFunction($function, $callback)
    {
        $original = $this->setEnabledFunctions(array_fill_keys((array)$function, false));
        $value = $callback();
        $this->setEnabledFunctions($original);
        return $value;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Helper method to access the private invoke method.
     *
     * @param string $function
     *   The function to invoke.
     * @param array $args
     *   Arguments to pass along to the function.
     * @param mixed $noneValue
     *   The value which should be considered "None"; null by default.
     * @param bool $throwException
     *   Optional. Flag indicating whether or not any exceptions should be thrown.
     *   If not set, the value that was set when creating the instance will be used.
     * @param bool $disable
     *   Flag indicating whether to "disable" the function before invoking it.
     *
     * @return mixed
     *   The return value of the invoked function.
     */
    protected function invoke(
        $function,
        array $args = array(),
        $throwException = true,
        $noneValue = null,
        $disable = false
    ) {
        $ref = new ReflectionMethod('\\UnicornFail\\PhpOption\\Utility\\Helper', 'invoke');
        $ref->setAccessible(true);
        if ($disable) {
            return $this->disableFunction($function, function () use (
                $ref,
                $function,
                $args,
                $noneValue,
                $throwException
            ) {
                return $ref->invoke(null, $function, $args, $throwException, $noneValue);
            });
        }
        return $ref->invoke(null, $function, $args, $throwException, $noneValue);
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * Helper method to disable a function and then access the private invoke method.
     *
     * @param string $function
     *   The function to invoke.
     * @param array $args
     *   Arguments to pass along to the function.
     * @param mixed $noneValue
     *   The value which should be considered "None"; null by default.
     * @param bool $throwException
     *   Optional. Flag indicating whether or not any exceptions should be thrown.
     *   If not set, the value that was set when creating the instance will be used.
     *
     * @return mixed
     *   The return value of the invoked function.
     */
    protected function invokeDisabled($function, array $args = array(), $throwException = true, $noneValue = null)
    {
        return $this->invoke($function, $args, $throwException, $noneValue, true);
    }
}
