<?php

/**
 * Copyright 2019 Mark Carver <mark.carver@me.com>
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
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

use ArrayAccess;
use ArrayObject;
use PHPUnit\Framework\TestCase;
use Traversable;
use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Option;
use UnicornFail\PhpOption\Some;
use UnicornFail\PhpOption\Tests\Fixtures\TestSome;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Option
 * @group core
 */
class OptionTest extends TestCase
{
    const OI = '\\UnicornFail\\PhpOption\\OptionInterface';
    const NONE = '\\UnicornFail\\PhpOption\\None';
    const SOME = '\\UnicornFail\\PhpOption\\Some';

    protected static $noneFalse = array(Option::NONE_VALUE => false);

    protected function createOption($value, array $options = array())
    {
        $option = Option::create($value, $options);
        $this->assertInstanceOf(static::OI, $option);
        return $option;
    }

    /**
     * @covers ::create
     * @covers ::getClass
     * @covers ::getStaticOption
     */
    public function testCreate()
    {
        $this->assertInstanceOf(static::NONE, $this->createOption(null));
        $this->assertInstanceOf(static::SOME, $this->createOption('value'));
        $this->assertInstanceOf(static::NONE, $this->createOption(false, static::$noneFalse));
        $this->assertInstanceOf(static::SOME, $this->createOption('value', static::$noneFalse));
        $this->assertInstanceOf(static::SOME, $this->createOption(null, static::$noneFalse));

        $option = $this->createOption(false);
        $this->assertTrue($option->isDefined());
        $this->assertSame(false, $option->get());
        $this->assertFalse($this->createOption(null)->isDefined());
        $this->assertFalse($this->createOption(false, static::$noneFalse)->isDefined());
    }

    /**
     * @covers ::create
     * @covers ::createFromCallable
     */
    public function testCreateFromCallable()
    {
        $option = $this->createOption(function () {
            return 1;
        });
        $this->assertTrue($option->isDefined());
        $this->assertSame(1, $option->get());
        $this->assertFalse($this->createOption(function () {
            return null;
        })->isDefined());
        $this->assertFalse($this->createOption(function () {
            return 1;
        }, array(Option::NONE_VALUE => 1))->isDefined());

        $option = $this->createOption(function () {
            return Some::create(1);
        });
        $this->assertTrue($option->isDefined());
        $this->assertSame(1, $option->get());

        $option = $this->createOption(function () {
            return None::create();
        });
        $this->assertFalse($option->isDefined());

        $option = $this->createOption(function () {
            return function () {
                return 'foo';
            };
        });
        $this->assertTrue($option->isDefined());
        $this->assertEquals('foo', $option->get());
    }

    /**
     * @covers ::create
     * @covers ::createFromOption
     */
    public function testCreateFromOption()
    {
        // Ensure if nothing has changed, it returned the same instance.
        $option1 = $this->createOption(false);
        $option2 = $this->createOption($option1);
        $this->assertSame($option1, $option2);

        // Ensure that if the none value changes, it returns None.
        $option1 = $this->createOption(false);
        $option2 = $this->createOption($option1, static::$noneFalse);
        $this->assertInstanceOf(static::NONE, $option2);

        // Ensure that if the class needs to change, it does.
        $option1 = $this->createOption(false);
        $option2 = TestSome::create($option1);
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\Tests\\Fixtures\\TestSome', $option2);
        $this->assertNotSame($option1, $option2);
    }

    /**
     * @covers ::find
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   The iterable to search.
     * @param mixed|callable $predicate
     *   The key to search for.
     * @param mixed $expected
     *   The expected value.
     *
     * @dataProvider providerFind
     */
    public function testFind($iterable, $predicate, $expected)
    {
        $option = Option::find($iterable, $predicate);
        $this->assertEquals($expected, $option->getOrElse(null));
    }

    /**
     * Provides data for ::testFind.
     *
     * @return array
     *   The test data.
     */
    public function providerFind()
    {
        $items = array('foo', 'bar', 'baz', 'qux', 'quz');
        $data = array(
            array(
                $items,
                'baz',
                'baz',
            ),
            array(
                new ArrayObject($items),
                'quz',
                'quz',
            ),
            array(
                $items,
                'foo',
                'foo',
            ),
            array(
                $items,
                function ($value) {
                    return $value === 'bar';
                },
                'bar',
            ),
            array(
                $items,
                'missing',
                null
            ),
        );
        return $data;
    }

    /**
     * @covers ::findKey
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   The iterable to search.
     * @param mixed|callable $predicate
     *   The key to search for.
     * @param mixed $expected
     *   The expected value.
     *
     * @dataProvider providerFindKey
     */
    public function testFindKey($iterable, $predicate, $expected)
    {
        $option = Option::findKey($iterable, $predicate);
        $this->assertEquals($expected, $option->getOrElse(null));
    }

    /**
     * Provides data for ::testFindKey.
     *
     * @return array
     *   The test data.
     */
    public function providerFindKey()
    {
        $items = array('foo', 'bar', 'baz', 'qux', 'quz');
        $data = array(
            array(
                $items,
                'baz',
                2,
            ),
            array(
                new ArrayObject($items),
                'quz',
                4,
            ),
            array(
                $items,
                'foo',
                0,
            ),
            array(
                $items,
                function ($value) {
                    return $value === 'bar';
                },
                1
            ),
            array(
                $items,
                'missing',
                null
            ),
        );
        return $data;
    }

    /**
     * @covers ::getDefaultOptions
     */
    public function testDefaultOptions()
    {
        $expected = array(
            'noneValue' => null,
            'throwExceptions' => true,
        );
        $this->assertSame($expected, Option::getDefaultOptions());
    }

    /**
     * @covers ::pick
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   The iterable to search.
     * @param mixed|callable $predicate
     *   The key to search for.
     * @param mixed $expected
     *   The expected value.
     *
     * @dataProvider providerPick
     */
    public function testPick($iterable, $predicate, $expected)
    {
        $option = Option::pick($iterable, $predicate);
        $this->assertEquals($expected, $option->getOrElse(null));
    }

    /**
     * Provides data for ::testPick.
     *
     * @return array
     *   The test data.
     */
    public function providerPick()
    {
        $items = array('foo', 'bar', 'baz', 'qux', 'quz');
        $data = array(
            array(
                $items,
                2,
                'baz',
            ),
            array(
                new ArrayObject($items),
                4,
                'quz',
            ),
            array(
                $items,
                0,
                'foo',
            ),
            array(
                $items,
                function ($value) {
                    return $value === 1;
                },
                'bar'
            ),
            array(
                $items,
                'missing',
                null
            ),
        );
        return $data;
    }

    /**
     * @covers ::pluck
     *
     * @param array|ArrayAccess|Traversable $iterable
     *   The iterable to search.
     * @param mixed|callable $predicate
     *   The key to search for.
     * @param mixed $expected
     *   The expected value.
     *
     * @dataProvider providerPluck
     */
    public function testPluck($iterable, $predicate, $expected)
    {
        $option = Option::pluck($iterable, $predicate);
        $this->assertEquals($expected, $option->getOrElse(null));
    }

    /**
     * Provides data for ::testPluck.
     *
     * @return array
     *   The test data.
     */
    public function providerPluck()
    {
        $names = array('foo', 'bar', 'baz', 'qux', 'quz');
        $items = array();
        foreach ($names as $id => $name) {
            if ($id % 2) {
                $items[] = new ArrayObject(array('id' => $id, 'name' => $name));
            } else {
                $items[] = array('id' => $id, 'name' => $name);
            }
        }
        $data = array(
            array(
                $items,
                'id',
                array(0, 1, 2, 3, 4)
            ),
            array(
                new ArrayObject($items),
                'name',
                array('foo', 'bar', 'baz', 'qux', 'quz')
            ),
            array(
                $items,
                array('id', 'name'),
                array(0, 1, 2, 3, 4)
            ),
            array(
                $items,
                function ($value) {
                    return $value === 'name';
                },
                array('foo', 'bar', 'baz', 'qux', 'quz')
            ),
            array(
                $items,
                'missing',
                null
            ),
        );
        return $data;
    }

    /**********************************************
     *
     * DEPRECATED METHODS FROM ORIGINAL PHPOPTION.
     *
     **********************************************/

    protected function ensure($value, $noneValue = null)
    {
        $option = Option::ensure($value, $noneValue);
        $this->assertInstanceOf(static::OI, $option);
        return $option;
    }

    /**
     * @covers ::ensure
     * @covers ::fromValue
     * @covers ::fromArraysValue
     * @covers ::fromReturn
     */
    public function testDeprecated()
    {
        $null = function () {
            return null;
        };
        $false = function () {
            return false;
        };
        $some = function () {
            return 'foo';
        };

        $this->assertTrue(Option::fromValue(null)->isEmpty());
        $this->assertFalse(Option::fromValue(1)->isEmpty());
        $this->assertTrue(Option::fromValue(false, false)->isEmpty());
        $this->assertTrue(Option::fromValue(false)->isDefined());
        $this->assertFalse(Option::fromValue('foo', 'foo')->isDefined());

        $this->assertTrue(Option::fromReturn($null)->isEmpty());
        $this->assertFalse(Option::fromReturn($false)->isEmpty());
        $this->assertTrue(Option::fromReturn($false, array(), false)->isEmpty());
        $this->assertTrue(Option::fromReturn($some)->isDefined());
        $this->assertFalse(Option::fromReturn($some, array(), 'foo')->isDefined());

        $this->assertTrue(Option::ensure(null)->isEmpty());
        $this->assertFalse(Option::ensure(1)->isEmpty());
        $this->assertTrue(Option::ensure(false, false)->isEmpty());
        $this->assertTrue(Option::ensure(false)->isDefined());
        $this->assertFalse(Option::ensure('foo', 'foo')->isDefined());
        $this->assertTrue(Option::ensure($null)->isEmpty());
        $this->assertFalse(Option::ensure($false)->isEmpty());
        $this->assertTrue(Option::ensure($false, false)->isEmpty());
        $this->assertTrue(Option::ensure($some)->isDefined());
        $this->assertFalse(Option::ensure($some, 'foo')->isDefined());

        $this->assertEquals(None::create(), Option::fromArraysValue('foo', 'bar'));
        $this->assertEquals(None::create(), Option::fromArraysValue(null, 'bar'));
        $this->assertEquals(None::create(), Option::fromArraysValue(array('foo' => 'bar'), 'baz'));
        $this->assertEquals(None::create(), Option::fromArraysValue(array('foo' => null), 'foo'));
        $this->assertEquals(Some::create('foo'), Option::fromArraysValue(array('foo' => 'foo'), 'foo'));
    }
}
