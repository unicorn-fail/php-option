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

namespace UnicornFail\PhpOption\Tests\Unit;

use UnicornFail\PhpOption\Tests\Fixtures\BrokenTypedOption;
use UnicornFail\PhpOption\Tests\Fixtures\EmptyTypedOption;
use UnicornFail\PhpOption\Tests\Framework\OptionTestCase;
use UnicornFail\PhpOption\TypedOption;

/**
 * Class OptionTest.
 *
 * @coversDefaultClass \UnicornFail\PhpOption\TypedOption
 */
class TypedOptionTest extends OptionTestCase
{
    /**
     * @covers ::create
     */
    public function testBrokenTypedOption()
    {
        $this->assertException('\\UnexpectedValueException', sprintf(
            'Provided class is not valid: %s. Must be an instance of %s.',
            '\\UnicornFail\\PhpOption\\Some',
            '\\UnicornFail\\PhpOption\\TypedOptionInterface'
        ));
        BrokenTypedOption::create('foo');
    }

    /**
     * @covers ::create
     * @covers \UnicornFail\PhpOption\Exceptions\MissingTypedOptionsException
     */
    public function testEmptyTypedOption()
    {
        $this->assertException('\\UnicornFail\\PhpOption\\Exceptions\\MissingTypedOptionsException',
            'UnicornFail\PhpOption\Tests\Fixtures\EmptyTypedOption ' .
            'has not defined any option types. This may indicate that Option::create() should be used instead.'
        );
        EmptyTypedOption::create('foo');
    }

    /**
     * @covers ::create
     *
     * @param string $value
     *   The value to test.
     * @param mixed $expected
     *   The expected value.
     * @param string $class
     *   The expected class name.
     *
     * @dataProvider providerCreate
     */
    public function testCreate($value, $expected, $class)
    {
        $option = TypedOption::create($value);
        $this->assertInstanceOf($class, $option);
        $this->assertEquals($expected, $option->getOrElse(null));
    }

    /**
     * Provides data for ::testCreate.
     *
     * @return array
     */
    public function providerCreate()
    {
        $data = array(
            // None.
            array(null, null, '\\UnicornFail\\PhpOption\\None'),

            // SomeFloat.
            array('3.33', 3.33, '\\UnicornFail\\PhpOption\\Some\\SomeFloat'),
            array('-42.9', -42.9, '\\UnicornFail\\PhpOption\\Some\\SomeFloat'),
            array('-9.25', -9.25, '\\UnicornFail\\PhpOption\\Some\\SomeFloat'),
            array(3.33, 3.33, '\\UnicornFail\\PhpOption\\Some\\SomeFloat'),
            array(-42.9, -42.9, '\\UnicornFail\\PhpOption\\Some\\SomeFloat'),
            array(-9.25, -9.25, '\\UnicornFail\\PhpOption\\Some\\SomeFloat'),

            // SomeInteger.
            array(0, 0, '\\UnicornFail\\PhpOption\\Some\\SomeInteger'),
            array(1, 1, '\\UnicornFail\\PhpOption\\Some\\SomeInteger'),
            array('0', 0, '\\UnicornFail\\PhpOption\\Some\\SomeInteger'),
            array('1', 1, '\\UnicornFail\\PhpOption\\Some\\SomeInteger'),

            // SomeBoolean.
            array(true, true, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array(false, false, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('on', true, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('ON', true, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('off', false, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('OFF', false, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('true', true, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('TRUE', true, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('false', false, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('FALSE', false, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('yes', true, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('YES', true, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('no', false, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),
            array('NO', false, '\\UnicornFail\\PhpOption\\Some\\SomeBoolean'),

            // SomeArray.
            array(array(), array(), '\\UnicornFail\\PhpOption\\Some\\SomeArray'),
            array(array(1, 2, 3), array(1, 2, 3), '\\UnicornFail\\PhpOption\\Some\\SomeArray'),
            array('foo,bar,baz', array('foo', 'bar', 'baz'), '\\UnicornFail\\PhpOption\\Some\\SomeArray'),
            array('foo=bar,baz=quz', array('foo' => 'bar', 'baz' => 'quz'), '\\UnicornFail\\PhpOption\\Some\\SomeArray'),

            // SomeString.
            array('', '', '\\UnicornFail\\PhpOption\\Some\\SomeString'),
            array('string', 'string', '\\UnicornFail\\PhpOption\\Some\\SomeString'),
            array('foo=bar', 'foo=bar', '\\UnicornFail\\PhpOption\\Some\\SomeString'),
        );

        $index = 0;
        return array_combine(array_map(function ($item) use (&$index) {
            $item = array_map(function ($value) {
                if (is_string($value) && (is_callable($value) || class_exists($value))) {
                    $parts = explode('\\', $value);
                    return array_pop($parts);
                }
                return json_encode($value);
            }, $item);
            $label = implode(' : ', array_merge(array('#' . $index++), $item));
            return $label;
        }, $data), $data);
    }
}
