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

use UnicornFail\PhpOption\Some\SomeFloat;
use UnicornFail\PhpOption\Tests\Framework\OptionTestCase;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Some\SomeFloat
 * @group typed
 */
class SomeFloatTest extends OptionTestCase
{
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
        $option = SomeFloat::create($raw);

        $class = $expected === null ? '\\UnicornFail\\PhpOption\\None' : '\\UnicornFail\\PhpOption\\Some\\SomeFloat';
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
        $data = array(
            // Valid values.
            array('double', '0.0', 0.0),
            array('double', '1.123', 1.123),
            array('double', '-0.5', -0.5),
            array('double', .875, .875),
            array('double', 456.42, 456.42),
            array('double', -456.42, -456.42),

            // Invalid values.
            array('null', '0', null),
            array('null', '1', null),
            array('null', '-0', null),
            array('null', 0, null),
            array('null', -1, null),
            array('null', 456, null),
            array('null', '', null),
            array('null', 'foo', null),
            array('null', '0G', null),
            array('null', '-0m', null),
            array('null', '3px', null),
            array('null', '#000000', null),
        );
        return array_combine(array_map(function ($item) {
            return $item[0] . ':' . $item[1];
        }, $data), $data);
    }

}