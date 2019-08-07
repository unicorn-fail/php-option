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

namespace UnicornFail\PhpOption\Tests\Unit\Some;

use UnicornFail\PhpOption\Some\SomeBoolean;
use UnicornFail\PhpOption\Tests\Framework\OptionTestCase;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Some\SomeBoolean
 * @group typed
 */
class SomeBooleanTest extends OptionTestCase
{
    /**
     * @covers ::getDefaultOptions
     */
    public function testDefaultOptions()
    {
        $expected = array(
            'noneValue' => null,
            'throwExceptions' => true,
            'falsy' => array('0', 'off', 'false', 'no'),
            'truthy' => array('1', 'on', 'true', 'yes'),
        );
        $this->assertSame($expected, SomeBoolean::getDefaultOptions());
    }

    /**
     * @covers ::applies
     * @covers ::create
     * @covers ::getValidTypes
     * @covers ::isFalsy
     * @covers ::isTruthy
     * @covers ::transformValue
     *
     * @param string $raw
     *   The raw value to test.
     * @param mixed $expected
     *   The expected value.
     * @param bool $expected2
     *   The expected value for the second assertion.
     *
     * @dataProvider providerTransform
     */
    public function testTransform($raw, $expected, $expected2 = null)
    {
        $option = SomeBoolean::create($raw);
        $actual = $option->getOrElse(null);
        $class = $expected === null ? '\\UnicornFail\\PhpOption\\None' : '\\UnicornFail\\PhpOption\\Some\\SomeBoolean';
        $this->assertInstanceOf($class, $option);
        $this->assertEquals($expected, $actual);

        // Ensure that the falsy and truthy values can be changed.
        $option = SomeBoolean::create($raw, array(
            SomeBoolean::TRUTHY => array('1', 'true'),
            SomeBoolean::FALSY => array('0', 'false'),
        ));
        $actual = $option->getOrElse(null);
        $class = $expected2 === null ? '\\UnicornFail\\PhpOption\\None' : '\\UnicornFail\\PhpOption\\Some\\SomeBoolean';
        $this->assertInstanceOf($class, $option);
        $this->assertEquals($expected2, $actual);
    }

    /**
     * Provides data for ::testTransform.
     *
     * @return array
     *   The test data.
     */
    public function providerTransform()
    {
        $data = array(
            // Binary.
            array('0', false, false),
            array('1', true, true),

            // On/Off.
            array('on', true),
            array('ON', true),
            array('off', false),
            array('OFF', false),

            // True/False.
            array('true', true, true),
            array('TRUE', true, true),
            array('false', false, false),
            array('FALSE', false, false),

            // Yes/No.
            array('yes', true),
            array('YES', true),
            array('no', false),
            array('NO', false),

            // Invalid booleans.
            array('', null),
            array('string', null),
            array('128M', null),
            array('3', null),
            array('3.33', null),
        );
        return array_combine(array_map(function ($item) {
            return reset($item) . ':' . json_encode($item[1]);
        }, $data), $data);
    }
}
