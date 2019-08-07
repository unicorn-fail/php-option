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

use PhpOption\Option;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
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
    public function testfromValueWithDefaultNoneValue()
    {
        $this->assertOptionInstance('PhpOption\None', \PhpOption\Option::fromValue(null));
        $this->assertOptionInstance('PhpOption\Some', \PhpOption\Option::fromValue('value'));
    }

    public function testFromValueWithFalseNoneValue()
    {
        $this->assertOptionInstance('PhpOption\None', \PhpOption\Option::fromValue(false, false));
        $this->assertOptionInstance('PhpOption\Some', \PhpOption\Option::fromValue('value', false));
        $this->assertOptionInstance('PhpOption\Some', \PhpOption\Option::fromValue(null, false));
    }

    public function testFromArraysValue()
    {
        $this->assertOptionInstance('PhpOption\None', Option::fromArraysValue('foo', 'bar'));
        $this->assertOptionInstance('PhpOption\None', Option::fromArraysValue(null, 'bar'));
        $this->assertOptionInstance('PhpOption\None', Option::fromArraysValue(array('foo' => 'bar'), 'baz'));
        $this->assertOptionInstance('PhpOption\None', Option::fromArraysValue(array('foo' => null), 'foo'));
        $this->assertOptionInstance('PhpOption\Some', Option::fromArraysValue(array('foo' => 'foo'), 'foo'));
    }

    public function testFromReturn()
    {
        $null = function() { return null; };
        $false = function() { return false; };
        $some = function() { return 'foo'; };

        $this->assertTrue(\PhpOption\Option::fromReturn($null)->isEmpty());
        $this->assertFalse(\PhpOption\Option::fromReturn($false)->isEmpty());
        $this->assertTrue(\PhpOption\Option::fromReturn($false, array(), false)->isEmpty());
        $this->assertTrue(\PhpOption\Option::fromReturn($some)->isDefined());
        $this->assertFalse(\PhpOption\Option::fromReturn($some, array(), 'foo')->isDefined());
    }

    public function testOrElse()
    {
        $a = new \PhpOption\Some('a');
        $b = new \PhpOption\Some('b');

        $this->assertEquals('a', $a->orElse($b)->get());
    }

    public function testOrElseWithNoneAsFirst()
    {
        $a = \PhpOption\None::create();
        $b = new \PhpOption\Some('b');

        $this->assertEquals('b', $a->orElse($b)->get());
    }

    public function testOrElseWithLazyOptions()
    {
        $throws = function() { throw new \LogicException('Should never be called.'); };

        $a = new \PhpOption\Some('a');
        $b = new \PhpOption\LazyOption($throws);

        $this->assertEquals('a', $a->orElse($b)->get());
    }

    public function testOrElseWithMultipleAlternatives()
    {
        $throws = new \PhpOption\LazyOption(function() { throw new \LogicException('Should never be called.'); });
        $returns = new \PhpOption\LazyOption(function() { return new \PhpOption\Some('foo'); });

        $a = \PhpOption\None::create();

        $this->assertEquals('foo', $a->orElse($returns)->orElse($throws)->get());
    }
}
