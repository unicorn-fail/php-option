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

namespace UnicornFail\PhpOption\Tests\Unit\Utility;

use ArrayObject;
use stdClass;
use UnicornFail\PhpOption\Tests\Framework\OptionTestCase;
use UnicornFail\PhpOption\Utility\Helper;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Utility\Helper
 * @group utility
 */
class HelperTest extends OptionTestCase
{
    /**
     * @covers ::explode
     *
     * @dataProvider providerExplode
     */
    public function testExplode($delimiter, $value, $expected, $limit = null)
    {
        $actual = Helper::explode($delimiter, $value, $limit);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provides data for ::testExplode.
     *
     * @return array
     *   The test data.
     */
    public function providerExplode()
    {
        $data[] = array(',', "       foo,bar,baz       ", array('foo', 'bar', 'baz'));
        $data[] = array('|', "     foo | bar | baz     ", array('foo', 'bar', 'baz'));
        $data[] = array('=', " ' foo '= 'bar' =' baz ' ", array('foo', 'bar', 'baz'));
        $data[] = array(',', "       foo,bar,baz       ", array('foo'), 1);
        $data[] = array(',', "     foo , bar , baz     ", array('foo'), 1);
        $data[] = array(',', " ' foo ', 'bar' ,' baz ' ", array('foo'), 1);
        $data[] = array(',', "       foo,bar,baz       ", array('foo', 'bar'), 2);
        $data[] = array(',', "     foo , bar , baz     ", array('foo', 'bar'), 2);
        $data[] = array(',', " ' foo ', 'bar' ,' baz ' ", array('foo', 'bar'), 2);
        $data[] = array(',', "       foo,bar,baz       ", array('bar', 'baz'), -2);
        $data[] = array(',', "     foo , bar , baz     ", array('bar', 'baz'), -2);
        $data[] = array(',', " ' foo ', 'bar' ,' baz ' ", array('bar', 'baz'), -2);
        $data[] = array(',', "       foo,bar,baz       ", array('baz'), -1);
        $data[] = array(',', "     foo , bar , baz     ", array('baz'), -1);
        $data[] = array(',', " ' foo ', 'bar' ,' baz ' ", array('baz'), -1);
        return $data;
    }

    /**
     * @covers ::getArrayObject
     *
     * @dataProvider providerGetArrayObject
     */
    public function testGetArrayObject($value, $expected)
    {
        $actual = Helper::getArrayObject($value);
        if ($expected === null) {
            $this->assertNull($actual);
        } else {
            $this->assertInstanceOf('\\ArrayObject', $actual);
            $this->assertEquals($expected, $actual->getArrayCopy());
        }
    }

    /**
     * Provides data for ::testExplode.
     *
     * @return array
     *   The test data.
     */
    public function providerGetArrayObject()
    {
        $data[] = array(array(1, 2, 3), array(1, 2, 3));
        $data[] = array(new ArrayObject(array(1, 2, 3)), array(1, 2, 3));
        $data[] = array(new stdClass(), null);
        $data[] = array(1, null);
        $data[] = array(null, null);
        return $data;
    }

    /**
     * @covers ::invoke
     * @covers \UnicornFail\PhpOption\Exceptions\InvalidCallableException
     * @expectedException \UnicornFail\PhpOption\Exceptions\InvalidCallableException
     * @expectedExceptionMessage Invalid callable "phpversion"
     */
    public function testInvoke()
    {
        $value = $this->invoke('phpversion');
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\Some', $value);

        $value = $this->invokeDisabled('phpversion', array(), false, false);
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $value);

        $this->invokeDisabled('phpversion');
    }

    /**
     * @covers ::predicate
     *
     * @dataProvider providerPredicate
     */
    public function testPredicate($predicate, $options, $value, $expected)
    {
        $original = $predicate;
        $assertSame = is_callable($predicate);
        $predicate = Helper::predicate($predicate, $options);
        $this->assertInstanceOf('\\Closure', $predicate);
        if ($assertSame) {
            $this->assertSame($predicate, $original);
        }
        $actual = $predicate($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provides data for ::testPredicate.
     *
     * @return array
     *   The test data.
     */
    public function providerPredicate()
    {
        $items[] = array(
            1,
            array('strict' => true),
            array(0, false),
            array('1', false),
            array('2', false),
            array(1, true),
            array(2, false),
        );
        $items[] = array(
            array(1, 2),
            array('strict' => true),
            array(0, false),
            array('1', false),
            array('2', false),
            array(1, true),
            array(2, true)
        );
        $items[] = array(
            1,
            array('strict' => false),
            array(0, false),
            array('1', true),
            array('2', false),
            array(1, true),
            array(2, false)
        );
        $items[] = array(
            function ($value) {
                return $value % 2 == 0;
            },
            array('strict' => false),
            array('3', false),
            array(1, false),
            array(4, true),
            array(2, true),
            array('2', true)
        );

        $data = array();
        foreach ($items as $item) {
            $predicate = array_shift($item);
            $options = array_shift($item);
            foreach ($item as $list) {
                list($value, $expected) = $list;
                $args = array($predicate, $options, $value, $expected);
                $key = implode(':', array_map('json_encode', $args));
                $data[$key] = $args;
            }
        }

        return $data;
    }

    /**
     * @covers ::toString
     *
     * @dataProvider providerToString
     */
    public function testToString($expected, $value, $namedScalars = true)
    {
        $actual = Helper::toString($value, $namedScalars);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provides data for ::testToString.
     *
     * @return array
     *   The test data.
     */
    public function providerToString()
    {
        // Scalars.
        $data[] = array('42', 42);
        $data[] = array('42.5', 42.5);
        $data[] = array('null', null);
        $data[] = array('true', true);
        $data[] = array('false', false);
        $data[] = array('', null, false);
        $data[] = array('1', true, false);
        $data[] = array('', false, false);
        $data[] = array('phpversion', 'phpversion');

        // Callables.
        $start = __LINE__ + 1;
        $closure = function () {
        };
        $end = __LINE__ - 1;
        $data[] = array("UnicornFail\\PhpOption\\Tests\\Unit\\Utility\\{closure}:$start:$end", $closure);
        $data[] = array('UnicornFail\\PhpOption\\Tests\\Unit\\Utility\\HelperTest', $this);
        $data[] = array(
            'UnicornFail\\PhpOption\\Tests\\Unit\\Utility\\HelperTest::testToString',
            array($this, 'testToString')
        );
        $data[] = array(
            'UnicornFail\\PhpOption\\Tests\\Unit\\Utility\\HelperTest::testToString',
            array('\\UnicornFail\\PhpOption\\Tests\\Unit\\Utility\\HelperTest', 'testToString'),
        );

        // Other.
        $data[] = array('bf9d7d8554074b7ebf8f01487ae10fac5e79db82c580d1f7b72539b5f279de20', array(1, 2, 3, 4));

        return $data;
    }

    /**
     * @covers ::trim
     *
     * @dataProvider providerTrim
     */
    public function testTrim($value, $expected)
    {
        $actual = Helper::trim($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provides data for ::testTrim.
     *
     * @return array
     *   The test data.
     */
    public function providerTrim()
    {
        $data[] = array("      foo      ", 'foo');
        $data[] = array("\t    foo    \n", 'foo');
        $data[] = array("     'foo'     ", 'foo');
        $data[] = array("\t  \"foo\"  \n", 'foo');
        $data[] = array("\t ' foo '     ", 'foo');
        $data[] = array("\n\" foo\"     ", 'foo');
        $data[] = array("     [ foo ]     ", '[ foo ]');
        $data[] = array("\t   [ foo ]   \n", '[ foo ]');
        $data[] = array("    '[ foo ] '   ", '[ foo ]');
        $data[] = array("\t\" [ foo ] \"\n", '[ foo ]');
        $data[] = array("\t ' [ foo ] '   ", '[ foo ]');
        $data[] = array("\n\" [ foo ] \"  ", '[ foo ]');
        return $data;
    }
}
