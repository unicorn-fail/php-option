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

use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnicornFail\PhpOption\LazyOption;
use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Some;
use UnicornFail\PhpOption\Tests\Fixtures\Repository;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\LazyOption
 */
class LazyOptionTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        $this->subject = $this
            ->getMockBuilder('Subject')
            ->setMethods(array('execute'))
            ->getMock();
    }

    /**
     * @covers ::__construct
     * @expectedException \UnicornFail\PhpOption\Exceptions\InvalidCallableException
     * @expectedExceptionMessage Invalid callable "invalidCallback"
     */
    public function testConstructorInvalidCallable()
    {
        new LazyOption('invalidCallback');
    }

    /**
     * @covers ::create
     * @expectedException \UnicornFail\PhpOption\Exceptions\InvalidCallableException
     * @expectedExceptionMessage Invalid callable "invalidCallback"
     */
    public function testCreateInvalidCallable()
    {
        LazyOption::create('invalidCallback');
    }

    /**
     * @covers ::filter
     */
    public function testFilter()
    {
        $some = Some::create('foo');
        $lazy = LazyOption::create(array($this->subject, 'execute'), array('foo'));
        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->with('foo')
            ->will($this->returnValue($some));

        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $lazy->filter(function ($v) {
            return 0 === strlen($v);
        }));
        $this->assertSame($some, $lazy->filter(function ($v) {
            return strlen($v) > 0;
        }));
    }

    /**
     * @covers ::filterNot
     */
    public function testFilterNot()
    {
        $some = Some::create('foo');
        $lazy = LazyOption::create(array($this->subject, 'execute'), array('foo'));
        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->with('foo')
            ->will($this->returnValue($some));

        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $lazy->filterNot(function ($v) {
            return strlen($v) > 0;
        }));
        $this->assertSame($some, $lazy->filterNot(function ($v) {
            return strlen($v) === 0;
        }));
    }

    /**
     * @covers ::flatMap
     */
    public function testFlatMap()
    {
        $repo = new Repository(array('foo'));

        $lazy = $repo->getLastRegisteredUsername(true);
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\LazyOption', $lazy);

        $this->assertEquals(array('name' => 'foo'), $lazy->flatMap(array($repo, 'getUser'))
            ->getOrCall(array($repo, 'getDefaultUser')));
    }

    /**
     * @covers ::getIterator
     */
    public function testForEach()
    {
        $lazy = LazyOption::create(function () {
            return Some::create('foo');
        });

        $called = 0;
        $extractedValue = null;
        foreach ($lazy as $value) {
            $extractedValue = $value;
            $called++;
        }

        $this->assertEquals('foo', $extractedValue);
        $this->assertEquals(1, $called);
    }

    /**
     * @covers ::create
     * @covers ::get
     * @covers ::getOrElse
     * @covers ::getOrCall
     * @covers ::getOrThrow
     * @covers ::isEmpty
     */
    public function testGetWithArgumentsAndConstructor()
    {
        $lazy = LazyOption::create(array($this->subject, 'execute'), array('foo'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->with('foo')
            ->will($this->returnValue(Some::create('foo')));

        $this->assertEquals('foo', $lazy->get());
        $this->assertEquals('foo', $lazy->getOrElse(null));
        $this->assertEquals('foo', $lazy->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $lazy->getOrThrow(new RuntimeException('does_not_exist')));
        $this->assertFalse($lazy->isEmpty());
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::getOrElse
     * @covers ::getOrCall
     * @covers ::getOrThrow
     * @covers ::isEmpty
     */
    public function testGetWithArgumentsAndCreate()
    {
        $lazy = new LazyOption(array($this->subject, 'execute'), array('foo'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->with('foo')
            ->will($this->returnValue(Some::create('foo')));

        $this->assertEquals('foo', $lazy->get());
        $this->assertEquals('foo', $lazy->getOrElse(null));
        $this->assertEquals('foo', $lazy->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $lazy->getOrThrow(new RuntimeException('does_not_exist')));
        $this->assertFalse($lazy->isEmpty());
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::getOrElse
     * @covers ::getOrCall
     * @covers ::getOrThrow
     * @covers ::isEmpty
     */
    public function testGetWithoutArgumentsAndConstructor()
    {
        $lazy = new LazyOption(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(Some::create('foo')));

        $this->assertEquals('foo', $lazy->get());
        $this->assertEquals('foo', $lazy->getOrElse(null));
        $this->assertEquals('foo', $lazy->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $lazy->getOrThrow(new RuntimeException('does_not_exist')));
        $this->assertFalse($lazy->isEmpty());
    }

    /**
     * @covers ::create
     * @covers ::get
     * @covers ::getOrElse
     * @covers ::getOrCall
     * @covers ::getOrThrow
     * @covers ::isDefined
     * @covers ::isEmpty
     */
    public function testGetWithoutArgumentsAndCreate()
    {
        $lazy = LazyOption::create(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(Some::create('foo')));

        $this->assertTrue($lazy->isDefined());
        $this->assertFalse($lazy->isEmpty());
        $this->assertEquals('foo', $lazy->get());
        $this->assertEquals('foo', $lazy->getOrElse(null));
        $this->assertEquals('foo', $lazy->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $lazy->getOrThrow(new RuntimeException('does_not_exist')));
    }

    /**
     * @covers ::option
     */
    public function testCallbackOnlyInvokedOnce()
    {
        $called = 0;
        $lazy = LazyOption::create(function () use (&$called) {
            $called++;
            return None::create();
        });
        $lazy->isDefined();
        $lazy->isDefined();
        $this->assertEquals(1, $called);
    }

    /**
     * @covers ::create
     * @covers ::get
     * @covers ::getOrElse
     * @covers ::getOrCall
     * @covers ::isDefined
     * @covers ::isEmpty
     * @expectedException \UnicornFail\PhpOption\Exceptions\NoValueException
     * @expectedExceptionMessage None has no value
     */
    public function testCallbackReturnsNull()
    {
        $lazy = LazyOption::create(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(None::create()));

        $this->assertFalse($lazy->isDefined());
        $this->assertTrue($lazy->isEmpty());
        $this->assertEquals('alt', $lazy->getOrElse('alt'));
        $this->assertEquals('alt', $lazy->getOrCall(function () {
            return 'alt';
        }));

        $lazy->get();
    }

    /**
     * @covers ::create
     * @covers ::isDefined
     * @covers ::option
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Lazy option callbacks must return an instance of \UnicornFail\PhpOption\OptionInterface.
     */
    public function testExceptionIsThrownIfCallbackReturnsNonOption()
    {
        $option = LazyOption::create(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(null));

        $this->assertFalse($option->isDefined());
    }

    /**
     * @covers ::forAll
     */
    public function testForAll()
    {
        $actual = null;
        $called = false;
        $expected = 'foo';
        $lazy = LazyOption::create(function () use ($expected) {
            return Some::create($expected);
        });
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\LazyOption', $lazy);

        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\Some',
            $lazy->forAll(function ($value) use (&$actual, &$called) {
                $actual = $value;
                $called = true;
            }));
        $this->assertEquals($expected, $actual);
        $this->assertTrue($called);
    }

    /**
     * @covers ::ifDefined
     */
    public function testIfDefined()
    {
        $actual = null;
        $called = false;
        $expected = 'foo';
        $lazy = LazyOption::create(function () use ($expected) {
            return Some::create($expected);
        });
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\LazyOption', $lazy);

        $this->assertNull($lazy->ifDefined(function ($value) use (&$actual, &$called) {
            $actual = $value;
            $called = true;
        }));
        $this->assertEquals($expected, $actual);
        $this->assertTrue($called);
    }

    /**
     * @covers ::orElse
     */
    public function testOrElse()
    {
        $some = Some::create('foo');
        $lazy = LazyOption::create(function () use ($some) {
            return $some;
        });
        $this->assertSame($some, $lazy->orElse(None::create()));
        $this->assertSame($some, $lazy->orElse(Some::create('bar')));
    }

    /**
     * @covers ::__construct
     * @covers ::foldLeft
     * @covers ::foldRight
     */
    public function testFoldLeftRight()
    {
        $callback = function () {
        };

        $option = $this->getMockForAbstractClass('\\UnicornFail\\PhpOption\\Option');
        $option->expects($this->once())
            ->method('foldLeft')
            ->with(5, $callback)
            ->will($this->returnValue(6));
        $lazy = new LazyOption(function () use ($option) {
            return $option;
        });
        $this->assertSame(6, $lazy->foldLeft(5, $callback));

        $option->expects($this->once())
            ->method('foldRight')
            ->with(5, $callback)
            ->will($this->returnValue(6));
        $lazy = new LazyOption(function () use ($option) {
            return $option;
        });
        $this->assertSame(6, $lazy->foldRight(5, $callback));
    }

    /**
     * @covers ::map
     */
    public function testMap()
    {
        $lazy = LazyOption::create(function () {
            return Some::create('foo');
        });
        $this->assertEquals('o', $lazy->map(function ($v) {
            return substr($v, 1, 1);
        })->get());
    }

    /**
     * @covers ::select
     */
    public function testSelect()
    {
        $some = Some::create('foo');
        $lazy = LazyOption::create(function () use ($some) {
            return $some;
        });
        $this->assertSame($some, $lazy->select('foo'));
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $lazy->select('bar'));
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $lazy->select(true));
    }

    /**
     * @covers ::reject
     */
    public function testReject()
    {
        $some = Some::create('foo');
        $lazy = LazyOption::create(function () use ($some) {
            return $some;
        });
        $this->assertSame($some, $lazy->reject(null));
        $this->assertSame($some, $lazy->reject(true));
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $lazy->reject('foo'));
    }
}
