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

use stdClass;
use UnicornFail\PhpOption\Some\SomeString;
use UnicornFail\PhpOption\Tests\Framework\OptionTestCase;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Some\SomeString
 * @group typed
 */
class SomeStringTest extends OptionTestCase
{
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return 'foo';
    }

    /**
     * @covers ::applies
     * @covers ::create
     * @covers ::getValidTypes
     * @covers ::transformValue
     *
     * @param mixed $type
     *   The expected value.
     * @param string $raw
     *   The value to test.
     * @param mixed $expected
     *   The expected value.
     *
     * @dataProvider providerTransform
     */
    public function testTransform($type, $raw, $expected)
    {
        $option = SomeString::create($raw);

        $class = $expected === null ? '\\UnicornFail\\PhpOption\\None' : '\\UnicornFail\\PhpOption\\Some\\SomeString';
        $this->assertInstanceOf($class, $option);

        $actual = $option->getOrElse(null);
        $this->assertEquals($type, strtolower(gettype($actual)));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provides data for ::testTransform.
     *
     * @return array
     *   The test data.
     */
    public function providerTransform()
    {
        $stdClass = new stdClass;
        $stdClassToString = new stdClass;
        $stdClassToString->__toString = function () {
            return 'foo';
        };

        $data = array(
            // Valid values.
            array('string', '', ''),
            array('string', '0', '0'),
            array('string', '0.0', '0.0'),
            array('string', '-0', '-0'),
            array('string', '1', '1'),
            array('string', '1.0', '1.0'),
            array('string', '1.123', '1.123'),
            array('string', '-0.5', '-0.5'),
            array('string', 0, '0'),
            array('string', 0.0, '0'),
            array('string', 1, '1'),
            array('string', 1.0, '1'),
            array('string', -1, '-1'),
            array('string', .875, '0.875'),
            array('string', 456.42, '456.42'),
            array('string', -456.42, '-456.42'),
            array('string', 456, '456'),
            array('string', 'foo', 'foo'),
            array('string', '0G', '0G'),
            array('string', '-0m', '-0m'),
            array('string', '3px', '3px'),
            array('string', '#000000', '#000000'),
            array('string', $this, 'foo'),

            // Invalid values.
            array('null', $stdClass, null),
            array('null', $stdClassToString, null),
        );
        return array_combine(array_map(function ($item) {
            return $item[0] . ':' . json_encode($item[1]);
        }, $data), $data);
    }
}
