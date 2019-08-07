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
use stdClass;
use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Some;

/**
 * @group performance
 */
class ZPerformanceTest extends TestCase
{
    protected static $output;
    protected $performanceIterations = 10000;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $output = &static::$output;
        // Only register once.
        if ($output === null)
        {
            $output = array();
            register_shutdown_function(function () use (&$output) {
                print "\n" . implode("\n", $output) . "\n";
            });
        }
    }

    protected static function debug()
    {
        $args = func_get_args();
        static::$output[] = call_user_func_array('sprintf', $args);
    }

    public function testNoneCase()
    {
        $traditionalTime = microtime(true);
        for ($i = 0; $i < $this->performanceIterations; $i++) {
            $result = $this->traditionalFind(false);
            if ($result === null) {
                $result = new stdClass();
            }
        }
        $traditionalTime = microtime(true) - $traditionalTime;

        $optionTime = microtime(true);
        for ($i = 0; $i < $this->performanceIterations; $i++) {
            $this->optionFind(false)->getOrElse(new stdClass);
        }
        $optionTime = microtime(true) - $optionTime;

        $overheadPerInvocation = ($optionTime - $traditionalTime) / $this->performanceIterations;
        $this->debug('Overhead per invocation (none case): %.9fs', $overheadPerInvocation);

        // This test is more for informational purposes, but all tests
        // must have at least one assertion for it to be considered valid.
        $this->assertTrue(true);
    }

    public function testSomeCase()
    {
        $traditionalTime = microtime(true);
        for ($i = 0; $i < $this->performanceIterations; $i++) {
            $result = $this->traditionalFind(true);
            if ($result === null) {
                $result = new stdClass();
            }
        }
        $traditionalTime = microtime(true) - $traditionalTime;

        $optionTime = microtime(true);
        for ($i = 0; $i < $this->performanceIterations; $i++) {
            $this->optionFind(true)->getOrElse(new stdClass);
        }
        $optionTime = microtime(true) - $optionTime;

        $overheadPerInvocation = ($optionTime - $traditionalTime) / $this->performanceIterations;
        $this->debug('Overhead per invocation (some case): %.9fs', $overheadPerInvocation);

        // This test is more for informational purposes, but all tests
        // must have at least one assertion for it to be considered valid.
        $this->assertTrue(true);
    }

    protected function traditionalFind($success)
    {
        if ($success) {
            return new stdClass;
        }
        return null;
    }

    protected function optionFind($success)
    {
        if ($success) {
            return Some::create(new stdClass);
        }
        return None::create();
    }
}
