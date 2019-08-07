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

use PhpOption\LazyOption;
use PHPUnit\Framework\TestCase;

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

    public function testGetWithArgumentsAndConstructor()
    {
        $some = \PhpOption\LazyOption::create(array($this->subject, 'execute'), array('foo'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->with('foo')
            ->will($this->returnValue(\PhpOption\Some::create('foo')));

        $this->assertEquals('foo', $some->get());
        $this->assertEquals('foo', $some->getOrElse(null));
        $this->assertEquals('foo', $some->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $some->getOrThrow(new \RuntimeException('does_not_exist')));
        $this->assertFalse($some->isEmpty());
    }

    public function testGetWithArgumentsAndCreate()
    {
        $some = new \PhpOption\LazyOption(array($this->subject, 'execute'), array('foo'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->with('foo')
            ->will($this->returnValue(\PhpOption\Some::create('foo')));

        $this->assertEquals('foo', $some->get());
        $this->assertEquals('foo', $some->getOrElse(null));
        $this->assertEquals('foo', $some->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $some->getOrThrow(new \RuntimeException('does_not_exist')));
        $this->assertFalse($some->isEmpty());
    }

    public function testGetWithoutArgumentsAndConstructor()
    {
        $some = new \PhpOption\LazyOption(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(\PhpOption\Some::create('foo')));

        $this->assertEquals('foo', $some->get());
        $this->assertEquals('foo', $some->getOrElse(null));
        $this->assertEquals('foo', $some->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $some->getOrThrow(new \RuntimeException('does_not_exist')));
        $this->assertFalse($some->isEmpty());
    }

    public function testGetWithoutArgumentsAndCreate()
    {
        $option = \PhpOption\LazyOption::create(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(\PhpOption\Some::create('foo')));

        $this->assertTrue($option->isDefined());
        $this->assertFalse($option->isEmpty());
        $this->assertEquals('foo', $option->get());
        $this->assertEquals('foo', $option->getOrElse(null));
        $this->assertEquals('foo', $option->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $option->getOrThrow(new \RuntimeException('does_not_exist')));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage None has no value
     */
    public function testCallbackReturnsNull()
    {
        $option = \PhpOption\LazyOption::create(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(\PhpOption\None::create()));

        $this->assertFalse($option->isDefined());
        $this->assertTrue($option->isEmpty());
        $this->assertEquals('alt', $option->getOrElse('alt'));
        $this->assertEquals('alt', $option->getOrCall(function(){return 'alt';}));

        $option->get();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Lazy option callbacks must return an instance of \UnicornFail\PhpOption\OptionInterface.
     */
    public function testExceptionIsThrownIfCallbackReturnsNonOption()
    {
        $option = \PhpOption\LazyOption::create(array($this->subject, 'execute'));

        $this->subject
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(null));

        $this->assertFalse($option->isDefined());
    }

    /**
     * @expectedException \UnicornFail\PhpOption\Exceptions\InvalidCallableException
     * @expectedExceptionMessage Invalid callable "invalidCallback"
     */
    public function testInvalidCallbackAndConstructor()
    {
        new \PhpOption\LazyOption('invalidCallback');
    }

    /**
     * @expectedException \UnicornFail\PhpOption\Exceptions\InvalidCallableException
     * @expectedExceptionMessage Invalid callable "invalidCallback"
     */
    public function testInvalidCallbackAndCreate()
    {
        \PhpOption\LazyOption::create('invalidCallback');
    }

    public function testifDefined()
    {
        $called = false;
        $self = $this;
        $this->assertNull(LazyOption::fromValue('foo')->ifDefined(function($v) use (&$called, $self) {
            $called = true;
            $self->assertEquals('foo', $v);
        }));
        $this->assertTrue($called);
    }

    public function testForAll()
    {
        $called = false;
        $self = $this;

        // Due to some weird bug in PHP 5.3, class_alias() doesn't work as expected.
        // Instead, to make it work, the classes have to be extended which breaks inheritance.
        // To get these tests working properly, the real class it's returning has to be verified.
        if (PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION === 3) {
            $class = 'UnicornFail\\PhpOption\\OptionInterface';
        } else {
            $class = 'PhpOption\\Some';
        }
        $this->assertInstanceOf($class, LazyOption::fromValue('foo')->forAll(function($v) use (&$called, $self) {
            $called = true;
            $self->assertEquals('foo', $v);
        }));
        $this->assertTrue($called);
    }

    public function testOrElse()
    {
        $some = \PhpOption\Some::create('foo');
        $lazy = \PhpOption\LazyOption::create(function() use ($some) {return $some;});
        $this->assertSame($some, $lazy->orElse(\PhpOption\None::create()));
        $this->assertSame($some, $lazy->orElse(\PhpOption\Some::create('bar')));
    }

    public function testFoldLeftRight()
    {
        $callback = function() { };

        $option = $this->getMockForAbstractClass('PhpOption\Option');
        $option->expects($this->once())
            ->method('foldLeft')
            ->with(5, $callback)
            ->will($this->returnValue(6));
        $lazyOption = new LazyOption(function() use ($option) { return $option; });
        $this->assertSame(6, $lazyOption->foldLeft(5, $callback));

        $option->expects($this->once())
            ->method('foldRight')
            ->with(5, $callback)
            ->will($this->returnValue(6));
        $lazyOption = new LazyOption(function() use ($option) { return $option; });
        $this->assertSame(6, $lazyOption->foldRight(5, $callback));
    }
}
