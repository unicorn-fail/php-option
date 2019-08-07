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

namespace UnicornFail\PhpOption\Tests\Fixtures;

use UnicornFail\PhpOption\LazyOption;
use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Some;

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
    public function getLastRegisteredUsername($lazy = false)
    {
        if (empty($this->users)) {
            return None::create();
        }
        if ($lazy) {
            $users = $this->users;
            return LazyOption::create(function () use ($users) {
                return Some::create(end($users));
            });
        }
        return Some::create(end($this->users));
    }

    // Returns a user object (we will live with an array here).
    public function getUser($name, $lazy = false)
    {
        if (in_array($name, $this->users, true)) {
            if ($lazy) {
                return LazyOption::create(function () use ($name) {
                    return Some::create(array('name' => $name));
                });
            }
            return Some::create(array('name' => $name));
        }
        return None::create();
    }

    public function getDefaultUser()
    {
        return array('name' => 'muhuhu');
    }
}
