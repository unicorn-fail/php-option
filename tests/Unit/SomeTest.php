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
use UnicornFail\PhpOption\LazyOption;
use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Some;
use UnicornFail\PhpOption\Tests\Fixtures\Repository;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Some
 */
class SomeTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::create
     * @covers ::doSet
     * @covers ::set
     * @covers ::setOptions
     */
    public function testCreate()
    {
        $none = Some::create(null);
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $none);

        $some = Some::create('foo');
        $this->assertEquals('foo', $some->get());
        $this->assertEquals('foo', $some->getOrElse(null));
        $this->assertEquals('foo', $some->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $some->getOrThrow(new \RuntimeException('Not found')));
        $this->assertFalse($some->isEmpty());
    }

    /**
     * @covers ::filter
     */
    public function testFilter()
    {
        $some = Some::create('foo');

        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $some->filter(function ($v) {
            return 0 === strlen($v);
        }));
        $this->assertSame($some, $some->filter(function ($v) {
            return strlen($v) > 0;
        }));
    }

    /**
     * @covers ::filterNot
     */
    public function testFilterNot()
    {
        $some = Some::create('foo');

        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $some->filterNot(function ($v) {
            return strlen($v) > 0;
        }));
        $this->assertSame($some, $some->filterNot(function ($v) {
            return strlen($v) === 0;
        }));
    }

    /**
     * @covers ::flatMap
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Callables passed to ::flatMap() must return an Option. Maybe you should use map() instead?
     */
    public function testFlatMap()
    {
        $repo = new Repository(array('foo'));

        $some = $repo->getLastRegisteredUsername();
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\Some', $some);

        $this->assertEquals(array('name' => 'foo'), $some->flatMap(array($repo, 'getUser'))
            ->getOrCall(array($repo, 'getDefaultUser')));

        Some::create(array())->flatMap(function () {
            return false;
        });
    }

    /**
     * @covers ::foldLeft
     * @covers ::foldRight
     */
    public function testFoldLeftRight()
    {
        $self = $this;
        $some = Some::create(5);

        $this->assertSame(6, $some->foldLeft(1, function ($a, $b) use ($self) {
            $self->assertEquals(1, $a);
            $self->assertEquals(5, $b);
            return $a + $b;
        }));

        $this->assertSame(6, $some->foldRight(1, function ($a, $b) use ($self) {
            $self->assertEquals(1, $b);
            $self->assertEquals(5, $a);
            return $a + $b;
        }));
    }

    /**
     * @covers ::forAll
     */
    public function testForAll()
    {
        $called = false;
        $self = $this;
        $some = Some::create('foo');
        $this->assertSame($some, $some->forAll(function ($v) use (&$called, $self) {
            $called = true;
            $self->assertEquals('foo', $v);
        }));
        $this->assertTrue($called);
    }

    /**
     * @covers ::doGet
     * @covers ::get
     * @covers ::getOrElse
     * @covers ::getOrCall
     * @covers ::getOrThrow
     * @covers ::isDefined
     * @covers ::isEmpty
     */
    public function testGet()
    {
        $some = Some::create('foo');
        $this->assertTrue($some->isDefined());
        $this->assertFalse($some->isEmpty());
        $this->assertEquals('foo', $some->get());
        $this->assertEquals('foo', $some->getOrElse(null));
        $this->assertEquals('foo', $some->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $some->getOrThrow(new \RuntimeException('Not found')));
        $this->assertFalse($some->isEmpty());
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIterator()
    {
        $some = Some::create('foo');

        $called = 0;
        $extractedValue = null;
        foreach ($some as $value) {
            $extractedValue = $value;
            $called++;
        }

        $this->assertEquals('foo', $extractedValue);
        $this->assertEquals(1, $called);
    }

    /**
     * @covers ::getOption
     * @covers ::getOptions
     */
    public function testGetOptions()
    {
        $option = Some::create(false);
        $expected = array(
            'noneValue' => null,
            'throwExceptions' => true,
        );
        $this->assertSame($expected, $option->getOptions());
        $this->assertTrue($option->getOption('throwExceptions'));
        $this->assertFalse($option->getOption('missing', false));
    }

    /**
     * @covers ::map
     */
    public function testMap()
    {
        $some = Some::create('foo');
        $this->assertEquals('o', $some->map(function ($v) {
            return substr($v, 1, 1);
        })->get());
    }

    /**
     * @covers ::orElse
     */
    public function testOrElse()
    {
        $some = Some::create('foo');
        $this->assertSame($some, $some->orElse(None::create()));
        $this->assertSame($some, $some->orElse(Some::create('bar')));

        $a = Some::create('a');
        $b = Some::create('b');
        $this->assertEquals('a', $a->orElse($b)->get());
    }

    /**
     * @covers ::orElse
     */
    public function testOrElseWithNoneAsFirst()
    {
        $a = None::create();
        $b = Some::create('b');

        $this->assertEquals('b', $a->orElse($b)->get());
    }

    /**
     * @covers ::orElse
     */
    public function testOrElseWithLazyOptions()
    {
        $throws = function () {
            throw new \LogicException('Should never be called.');
        };

        $a = Some::create('a');
        $b = new LazyOption($throws);

        $this->assertEquals('a', $a->orElse($b)->get());
    }

    /**
     * @covers ::orElse
     */
    public function testOrElseWithMultipleAlternatives()
    {
        $throws = new LazyOption(function () {
            throw new \LogicException('Should never be called.');
        });
        $returns = new LazyOption(function () {
            return Some::create('foo');
        });

        $a = None::create();

        $this->assertEquals('foo', $a->orElse($returns)->orElse($throws)->get());
    }

    /**
     * @covers ::reject
     */
    public function testReject()
    {
        $some = Some::create('foo');

        $this->assertSame($some, $some->reject(null));
        $this->assertSame($some, $some->reject(true));
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $some->reject('foo'));
    }

    /**
     * @covers ::select
     */
    public function testSelect()
    {
        $some = Some::create('foo');

        $this->assertSame($some, $some->select('foo'));
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $some->select('bar'));
        $this->assertInstanceOf('\\UnicornFail\\PhpOption\\None', $some->select(true));
    }

    /**
     * @covers ::setOption
     * @covers ::setOptions
     */
    public function testSetOptions()
    {
        $option = Some::create(false);
        $expected = array(
            'noneValue' => null,
            'throwExceptions' => true,
        );
        $this->assertSame($expected, $option->getOptions());
        $expected['newOption'] = 'foo';

        $option->setOptions($expected);
        $this->assertSame($expected, $option->getOptions());
        $this->assertEquals('foo', $option->getOption('newOption'));

        $option->setOption('newOption', 'bar');
        $this->assertEquals('bar', $option->getOption('newOption'));
    }
}
