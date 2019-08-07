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

use ArrayObject;
use UnicornFail\PhpOption\Some\SomeArray;
use UnicornFail\PhpOption\Tests\Framework\OptionTestCase;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Some\SomeArray
 * @group typed
 */
class SomeArrayTest extends OptionTestCase
{
    /**
     * @covers ::getDefaultOptions
     */
    public function testDefaultOptions()
    {
        $expected = array(
            'noneValue' => null,
            'throwExceptions' => true,
            'keyDelimiter' => '=',
            'listDelimiter' => ',',
        );
        $this->assertSame($expected, SomeArray::getDefaultOptions());
    }

    /**
     * @covers ::applies
     * @covers ::create
     * @covers ::getValidTypes
     * @covers ::transformValue
     *
     * @param string $listDelimiter
     *   The delimiter to use for splitting the value.
     * @param string $keyDelimiter
     *   The delimiter to use for splitting key/value pairs.
     * @param string $raw
     *   The raw value to test.
     * @param mixed $expected
     *   The expected value.
     *
     * @dataProvider providerTransform
     */
    public function testTransform($listDelimiter, $keyDelimiter, $raw, $expected)
    {
        $option = SomeArray::create($raw, array(
            SomeArray::LIST_DELIMITER => $listDelimiter,
            SomeArray::KEY_DELIMITER => $keyDelimiter,
        ));

        $class = $expected === null ? '\\UnicornFail\\PhpOption\\None' : '\\UnicornFail\\PhpOption\\Some\\SomeArray';
        $this->assertInstanceOf($class, $option);
        $this->assertEquals($expected, $option->getOrElse(null));
    }

    /**
     * Provides data for ::testTransform.
     *
     * @return array
     *   The test data.
     */
    public function providerTransform()
    {
        $object = new ArrayObject(array(4, 5, 6));
        $data = array(
            // Arrays.
            array(',', '=', array(), array(), '\\UnicornFail\\PhpOption\\Some\\SomeArray'),
            array(',', '=', array(1, 2, 3), array(1, 2, 3), '\\UnicornFail\\PhpOption\\Some\\SomeArray'),
            array(',', '=', $object, array(4, 5, 6), '\\UnicornFail\\PhpOption\\Some\\SomeArray'),

            // No key/value pairs (index array).
            array(',', '=', 'foo,bar,baz', array('foo', 'bar', 'baz')),
            array(',', '=', ' foo , bar , baz ', array('foo', 'bar', 'baz')),
            array(',', '=', ' foo  bar , baz ', array('foo  bar', 'baz')),

            array(':', '=', 'foo:bar:baz', array('foo', 'bar', 'baz')),
            array(':', '=', ' foo : bar : baz ', array('foo', 'bar', 'baz')),
            array(':', '=', ' foo  bar : baz ', array('foo  bar', 'baz')),

            // Key/value pairs (associative).
            array(',', '=', 'foo=bar,baz=quz', array('foo' => 'bar', 'baz' => 'quz')),
            array(',', '=', ' foo = bar , baz = quz ', array('foo' => 'bar', 'baz' => 'quz')),
            array(',', '=', ' foo  bar= baz , quz =foo', array('foo  bar' => 'baz', 'quz' => 'foo')),

            array('|', '=', 'foo=bar|baz=quz', array('foo' => 'bar', 'baz' => 'quz')),
            array('|', '=', ' foo = bar | baz = quz ', array('foo' => 'bar', 'baz' => 'quz')),
            array('|', '=', ' foo  bar= baz | quz =foo', array('foo  bar' => 'baz', 'quz' => 'foo')),

            array(' ', ':', 'foo:bar baz:quz', array('foo' => 'bar', 'baz' => 'quz')),
            array(';', ':', 'foo : bar;  baz: quz', array('foo' => 'bar', 'baz' => 'quz')),
            array(',', '=', 'foo = bar , baz, quz', array('foo' => 'bar', 'baz', 'quz')),

            // Nested key/value pairs (associative and indexed).
            array(',', '=', 'foo[]=bar,foo[]=baz,foo[]=quz', array('foo' => array('bar', 'baz', 'quz'))),
            array(',', '=', 'foo[bar]=baz,foo[]=quz,foo[]=foo', array('foo' => array('bar' => 'baz', 'quz', 'foo'))),
            array(',', '=', 'foo[bar]=baz,foo[bar]=quz,foo[bar]=foo', array('foo' => array('bar' => 'foo'))),
            array(';', ':', 'foo[]:bar; foo[]:baz; foo[]:quz', array('foo' => array('bar', 'baz', 'quz'))),
            array(';', ':', 'foo[bar]:baz;foo[]:quz;foo[]:foo', array('foo' => array('bar' => 'baz', 'quz', 'foo'))),
            array(';', ':', 'foo[bar]:baz;foo[bar]:quz;foo[bar]:foo', array('foo' => array('bar' => 'foo'))),

            // Quote wrapped key/value pairs.
            array(',', '=', 'foo="bar",baz="quz"', array('foo' => 'bar', 'baz' => 'quz')),
            array(',', '=', '\'foo\'="bar", \'baz\'="quz"', array('foo' => 'bar', 'baz' => 'quz')),
            array(',', '=', " foo = 'bar' , baz = 'quz' ", array('foo' => 'bar', 'baz' => 'quz')),
            array(',', '=', ' "foo  bar"= baz , "quz" =foo', array('foo  bar' => 'baz', 'quz' => 'foo')),

            // Invalid list (initial delimiter not present).
            array('|', '=', 'foo,bar,baz', null),
            array('|', '=', ' foo , bar , baz ', null),
            array('|', '=', ' foo  bar , baz ', null),
            array('|', '=', 'foo=bar,baz=quz', null),
            array('|', '=', ' foo = bar , baz = quz ', null),
            array('|', '=', ' foo  bar= baz , quz =foo', null),
        );
        return array_combine(array_map(function ($item) {
            return '(' . $item[0] . ' ' . $item[1] . ') ' . json_encode($item[2]);
        }, $data), $data);
    }

}
