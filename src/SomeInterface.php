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

namespace UnicornFail\PhpOption;

interface SomeInterface extends OptionInterface
{
    /**
     * Retrieves a specific option.
     *
     * @param string $name
     *   The name of the option to retrieve.
     * @param mixed $default
     *   The default value to use if option is not set.
     *
     * @return mixed
     *   The option value or $default if not set.
     */
    public function getOption($name, $default = null);

    /**
     * Retrieves the options that are currently set.
     *
     * @return array
     *   An associative array of options.
     */
    public function getOptions();

    /**
     * Sets a new value.
     *
     * @param mixed $value
     *   The value to set.
     *
     * @return SomeInterface|OptionInterface
     */
    public function set($value = null);

    /**
     * Sets a specific option.
     *
     * @param string $name
     *   The name of the option to set.
     * @param mixed $value
     *   The value to set.
     *
     * @return SomeInterface|OptionInterface
     */
    public function setOption($name, $value = null);

    /**
     * Sets the current options.
     *
     * @param array $options
     *   An array of options that will be merged onto the current settings.
     *
     * @return SomeInterface|OptionInterface
     */
    public function setOptions(array $options = array());
}
