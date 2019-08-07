<?php

/**
 * Copyright 2019 Mark Carver <mark.carver@me.com>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace UnicornFail\PhpOption\Tests\Unit\Some;

use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Option;
use UnicornFail\PhpOption\Some;
use UnicornFail\PhpOption\Some\SomeType;
use UnicornFail\PhpOption\Tests\Fixtures\TestSome;
use UnicornFail\PhpOption\Tests\Fixtures\TestSomeType;
use UnicornFail\PhpOption\Tests\Fixtures\TestSomeTypeTransformNone;
use UnicornFail\PhpOption\Tests\Fixtures\TestSomeTypeNotApplies;
use UnicornFail\PhpOption\Tests\Fixtures\TestSomeTypeValidTypes;
use UnicornFail\PhpOption\Tests\Framework\OptionTestCase;

/**
 * @coversDefaultClass \UnicornFail\PhpOption\Some\SomeType
 * @group typed
 */
class SomeTypeTest extends OptionTestCase
{
    const OI = '\\UnicornFail\\PhpOption\\OptionInterface';
    const NONE = '\\UnicornFail\\PhpOption\\None';
    const SOME = '\\UnicornFail\\PhpOption\\Some';

    protected static $noneFalse = array(Option::NONE_VALUE => false);

    protected function createOption($value, array $options = array())
    {
        $option = TestSomeType::create($value, $options);
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
        $this->assertInstanceOf(static::SOME, SomeType::create('value'));
        $this->assertInstanceOf(static::NONE, TestSomeTypeNotApplies::create('value'));
        $this->assertInstanceOf(static::NONE, TestSomeTypeTransformNone::create('value'));

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
     * @covers ::doSet
     * @covers ::getValidTypes
     * @covers ::getValueType
     */
    public function testDoSet() {
        // Test that exception can be silenced.
        $option = TestSomeTypeValidTypes::create(true, array(Option::THROW_EXCEPTIONS => false));
        $this->assertEquals('boolean', $option->getValueType());
        $option->set('test');
        $this->assertEquals('boolean', $option->getValueType());
        $this->assertEquals(true, $option->get());

        // Now test that exception is thrown by default.
        $this->assertException('\\InvalidArgumentException',
            'Invalid value type passed: string. Must be one of the following type(s): boolean. ' .
            'Use UnicornFail\PhpOption\Tests\Fixtures\TestSomeTypeValidTypes::create() instead.'
        );
        $option = TestSomeTypeValidTypes::create(true);
        $this->assertEquals('boolean', $option->getValueType());
        $option->set('test');
    }
}
