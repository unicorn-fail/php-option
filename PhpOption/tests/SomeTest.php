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

use PhpOption\Some;
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    protected function assertOptionInstance($expected, $actual) {
        // Due to some weird bug in PHP 5.3, class_alias() doesn't work as expected.
        // Instead, to make it work, the classes have to be extended which breaks inheritance.
        // To get these tests working properly, the real class it's returning has to be verified.
        if (PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION === 3) {
            $this->assertInstanceOf("UnicornFail\\$expected", $actual);
        } else {
            $this->assertInstanceOf($expected, $actual);
        }

    }

    public function testGet()
    {
        $some = new \PhpOption\Some('foo');
        $this->assertEquals('foo', $some->get());
        $this->assertEquals('foo', $some->getOrElse(null));
        $this->assertEquals('foo', $some->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $some->getOrThrow(new \RuntimeException('Not found')));
        $this->assertFalse($some->isEmpty());
    }

    public function testCreate()
    {
        $some = \PhpOption\Some::create('foo');
        $this->assertEquals('foo', $some->get());
        $this->assertEquals('foo', $some->getOrElse(null));
        $this->assertEquals('foo', $some->getOrCall('does_not_exist'));
        $this->assertEquals('foo', $some->getOrThrow(new \RuntimeException('Not found')));
        $this->assertFalse($some->isEmpty());
    }

    public function testOrElse()
    {
        $some = \PhpOption\Some::create('foo');
        $this->assertSame($some, $some->orElse(\PhpOption\None::create()));
        $this->assertSame($some, $some->orElse(\PhpOption\Some::create('bar')));
    }

    public function testifDefined()
    {
        $called = false;
        $self = $this;
        $some = new Some('foo');
        $this->assertNull($some->ifDefined(function($v) use (&$called, $self) {
            $called = true;
            $self->assertEquals('foo', $v);
        }));
        $this->assertTrue($called);
    }

    public function testForAll()
    {
        $called = false;
        $self = $this;
        $some = new Some('foo');
        $this->assertSame($some, $some->forAll(function($v) use (&$called, $self) {
            $called = true;
            $self->assertEquals('foo', $v);
        }));
        $this->assertTrue($called);
    }

    public function testMap()
    {
        $some = new Some('foo');
        $this->assertEquals('o', $some->map(function($v) { return substr($v, 1, 1); })->get());
    }

    public function testFlatMap()
    {
        $repo = new Repository(array('foo'));

        $this->assertEquals(array('name' => 'foo'), $repo->getLastRegisteredUsername()
                                                        ->flatMap(array($repo, 'getUser'))
                                                        ->getOrCall(array($repo, 'getDefaultUser')));
    }

    public function testFilter()
    {
        $some = new Some('foo');

        $this->assertOptionInstance('PhpOption\None', $some->filter(function($v) { return 0 === strlen($v); }));
        $this->assertSame($some, $some->filter(function($v) { return strlen($v) > 0; }));
    }

    public function testFilterNot()
    {
        $some = new Some('foo');

        $this->assertOptionInstance('PhpOption\None', $some->filterNot(function($v) { return strlen($v) > 0; }));
        $this->assertSame($some, $some->filterNot(function($v) { return strlen($v) === 0; }));
    }

    public function testSelect()
    {
        $some = new Some('foo');

        $this->assertSame($some, $some->select('foo'));
        $this->assertOptionInstance('PhpOption\None', $some->select('bar'));
        $this->assertOptionInstance('PhpOption\None', $some->select(true));
    }

    public function testReject()
    {
        $some = new Some('foo');

        $this->assertSame($some, $some->reject(null));
        $this->assertSame($some, $some->reject(true));
        $this->assertOptionInstance('PhpOption\None', $some->reject('foo'));
    }

    public function testFoldLeftRight()
    {
        $some = new Some(5);

        $self = $this;
        $this->assertSame(6, $some->foldLeft(1, function($a, $b) use ($self) {
            $self->assertEquals(1, $a);
            $self->assertEquals(5, $b);

            return $a + $b;
        }));

        $this->assertSame(6, $some->foldRight(1, function($a, $b) use ($self) {
            $self->assertEquals(1, $b);
            $self->assertEquals(5, $a);

            return $a + $b;
        }));
    }

    public function testForeach()
    {
        $some = new Some('foo');

        $called = 0;
        $extractedValue = null;
        foreach ($some as $value) {
            $extractedValue = $value;
            $called++;
        }

        $this->assertEquals('foo', $extractedValue);
        $this->assertEquals(1, $called);
    }
}

// For the interested reader of these tests, we have gone some great lengths
// to come up with a non-contrived example that might also be used in the
// real-world, and not only for testing purposes :)
class Repository
{
    private $users;

    public function __construct(array $users = array())
    {
        $this->users = $users;
    }

    // A fast ID lookup, probably cached, sometimes we might not need the entire user.
    public function getLastRegisteredUsername()
    {
        if (empty($this->users)) {
            return \PhpOption\None::create();
        }

        return new Some(end($this->users));
    }

    // Returns a user object (we will live with an array here).
    public function getUser($name)
    {
        if (in_array($name, $this->users, true)) {
            return new Some(array('name' => $name));
        }

        return \PhpOption\None::create();
    }

    public function getDefaultUser()
    {
        return array('name' => 'muhuhu');
    }
}
