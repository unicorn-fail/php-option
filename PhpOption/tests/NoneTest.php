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

namespace PhpOption\Tests;

use PhpOption\None;
use PHPUnit\Framework\TestCase;

/**
 * @group bc
 */
class NoneTest extends TestCase
{
    private $none;

    /**
     * @expectedException \RuntimeException
     */
    public function testGet()
    {
        $none = \PhpOption\None::create();
        $none->get();
    }

    public function testGetOrElse()
    {
        $none = \PhpOption\None::create();
        $this->assertEquals('foo', $none->getOrElse('foo'));
    }

    public function testGetOrCall()
    {
        $none = \PhpOption\None::create();
        $this->assertEquals('foo', $none->getOrCall(function() { return 'foo'; }));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not Found!
     */
    public function testGetOrThrow()
    {
        None::create()->getOrThrow(new \RuntimeException('Not Found!'));
    }

    public function testIsEmpty()
    {
        $none = \PhpOption\None::create();
        $this->assertTrue($none->isEmpty());
    }

    public function testOrElse()
    {
        $option = \PhpOption\Some::create('foo');
        $this->assertSame($option, \PhpOption\None::create()->orElse($option));
    }

    public function testifDefined()
    {
        $this->assertNull($this->none->ifDefined(function() {
            throw new \LogicException('Should never be called.');
        }));
    }

    public function testForAll()
    {
        $this->assertSame($this->none, $this->none->forAll(function() {
            throw new \LogicException('Should never be called.');
        }));
    }

    public function testMap()
    {
        $this->assertSame($this->none, $this->none->map(function() {
            throw new \LogicException('Should not be called.');
        }));
    }

    public function testFlatMap()
    {
        $this->assertSame($this->none, $this->none->flatMap(function() {
            throw new \LogicException('Should not be called.');
        }));
    }

    public function testFilter()
    {
        $this->assertSame($this->none, $this->none->filter(function() {
            throw new \LogicException('Should not be called.');
        }));
    }

    public function testFilterNot()
    {
        $this->assertSame($this->none, $this->none->filterNot(function() {
            throw new \LogicException('Should not be called.');
        }));
    }

    public function testSelect()
    {
        $this->assertSame($this->none, $this->none->select(null));
    }

    public function testReject()
    {
        $this->assertSame($this->none, $this->none->reject(null));
    }

    public function testForeach()
    {
        $none = \PhpOption\None::create();

        $called = 0;
        foreach ($none as $value) {
            $called++;
        }

        $this->assertEquals(0, $called);
    }

    public function testFoldLeftRight()
    {
        $this->assertSame(1, $this->none->foldLeft(1, function() { $this->fail(); }));
        $this->assertSame(1, $this->none->foldRight(1, function() { $this->fail(); }));
    }

    protected function setUp()
    {
        $this->none = None::create();
    }
}
