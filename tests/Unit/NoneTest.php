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

use LogicException;
use PHPUnit\Framework\TestCase;
use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Some;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\None
 * @group core
 */
class NoneTest extends TestCase
{
    /**
     * @var None
     */
    protected $none;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->none = None::create();
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        // Empty out static from setUp().
        $ref = new \ReflectionProperty('\\UnicornFail\\PhpOption\\None', 'instance');
        $ref->setAccessible(true);
        $ref->setValue(null, null);
        $this->assertNull($ref->getValue(null));

        // Ensure a new instance is statically cached.
        $first = None::create();
        $this->assertSame($ref->getValue(null), $first);

        // Ensure that another call retrieves the same static object.
        $second = None::create();
        $this->assertSame($ref->getValue(null), $second);

        // Ensure they're both the same object.
        $this->assertSame($first, $second);
    }

    /**
     * @covers ::get
     * @covers \UnicornFail\PhpOption\Exceptions\NoValueException
     * @expectedException \UnicornFail\PhpOption\Exceptions\NoValueException
     * @expectedExceptionMessage None has no value.
     */
    public function testGet()
    {
        $this->none->get();
    }

    /**
     * @covers ::getOrElse
     */
    public function testGetOrElse()
    {
        $this->assertEquals('foo', $this->none->getOrElse('foo'));
    }

    /**
     * @covers ::getOrCall
     */
    public function testGetOrCall()
    {
        $this->assertEquals('foo', $this->none->getOrCall(function () {
            return 'foo';
        }));
    }

    /**
     * @covers ::getOrThrow
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not Found!
     */
    public function testGetOrThrow()
    {
        $this->none->getOrThrow(new \RuntimeException('Not Found!'));
    }

    /**
     * @covers ::isDefined
     */
    public function testIsDefined()
    {
        $this->assertFalse($this->none->isDefined());
    }

    /**
     * @covers ::isEmpty
     */
    public function testIsEmpty()
    {
        $this->assertTrue($this->none->isEmpty());
    }

    /**
     * @covers ::orElse
     */
    public function testOrElse()
    {
        $option = Some::create('foo');
        $this->assertSame($option, $this->none->orElse($option));
    }

    /**
     * @covers ::forAll
     */
    public function testForAll()
    {
        $this->assertSame($this->none, $this->none->forAll(function () {
            throw new LogicException('Should never be called.');
        }));
    }

    /**
     * @covers ::map
     */
    public function testMap()
    {
        $this->assertSame($this->none, $this->none->map(function () {
            throw new LogicException('Should not be called.');
        }));
    }

    /**
     * @covers ::flatMap
     */
    public function testFlatMap()
    {
        $this->assertSame($this->none, $this->none->flatMap(function () {
            throw new LogicException('Should not be called.');
        }));
    }

    /**
     * @covers ::filter
     */
    public function testFilter()
    {
        $this->assertSame($this->none, $this->none->filter(function () {
            throw new LogicException('Should not be called.');
        }));
    }

    /**
     * @covers ::filterNot
     */
    public function testFilterNot()
    {
        $this->assertSame($this->none, $this->none->filterNot(function () {
            throw new LogicException('Should not be called.');
        }));
    }

    /**
     * @covers ::select
     */
    public function testSelect()
    {
        $this->assertSame($this->none, $this->none->select(null));
    }

    /**
     * @covers ::reject
     */
    public function testReject()
    {
        $this->assertSame($this->none, $this->none->reject(null));
    }

    /**
     * @covers ::getIterator
     */
    public function testForeach()
    {
        $called = 0;
        foreach ($this->none as $value) {
            $called++;
        }
        $this->assertEquals(0, $called);
    }

    /**
     * @covers ::foldLeft
     * @covers ::foldRight
     */
    public function testFoldLeftRight()
    {
        $self = $this;
        $this->assertSame(1, $this->none->foldLeft(1, function () use ($self) {
            $self->fail();
        }));
        $this->assertSame(1, $this->none->foldRight(1, function () use ($self) {
            $self->fail();
        }));
    }
}
