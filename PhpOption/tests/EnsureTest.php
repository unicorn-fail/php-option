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
use PhpOption\Option;
use PhpOption\Some;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Option::ensure() method
 *
 * @covers \PhpOption\Option::ensure
 * @group bc
 */
class EnsureTest extends TestCase
{
    protected function ensure($value, $noneValue = null)
    {
        $option = Option::ensure($value, $noneValue);
        // Due to some weird bug in PHP 5.3, class_alias() doesn't work as expected.
        // Instead, to make it work, the classes have to be extended which breaks inheritance.
        // To get these tests working properly, the real class it's returning has to be verified.
        if (PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION === 3) {
            $this->assertInstanceOf('UnicornFail\\PhpOption\\OptionInterface', $option);
        } else {
            $this->assertInstanceOf('PhpOption\\Option', $option);
        }
        return $option;
    }

    public function testMixedValue()
    {
        $option = $this->ensure(1);
        $this->assertTrue($option->isDefined());
        $this->assertSame(1, $option->get());
        $this->assertFalse($this->ensure(null)->isDefined());
        $this->assertFalse($this->ensure(1,1)->isDefined());
    }

    public function testReturnValue()
    {
        $option = $this->ensure(function() { return 1; });
        $this->assertTrue($option->isDefined());
        $this->assertSame(1, $option->get());
        $this->assertFalse($this->ensure(function() { return null; })->isDefined());
        $this->assertFalse($this->ensure(function() { return 1; }, 1)->isDefined());
    }

    public function testOptionReturnsAsSameInstance()
    {
        $option = $this->ensure(1);
        $this->assertSame($option, $this->ensure($option));
    }

    public function testOptionReturnedFromClosure()
    {
        $option = $this->ensure(function() { return Some::create(1); });
        $this->assertTrue($option->isDefined());
        $this->assertSame(1, $option->get());

        $option = $this->ensure(function() { return None::create(); });
        $this->assertFalse($option->isDefined());
    }

    public function testClosureReturnedFromClosure()
    {
        $option = $this->ensure(function() { return function() {}; });
        $this->assertTrue($option->isDefined());
        $this->assertInstanceOf('Closure', $option->get());
    }
}
