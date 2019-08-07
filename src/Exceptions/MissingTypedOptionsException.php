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

namespace UnicornFail\PhpOption\Exceptions;

use LogicException;
use UnicornFail\PhpOption\Utility\Helper;

class MissingTypedOptionsException extends LogicException
{
    /**
     * MissingTypedOptionsException constructor.
     *
     * @param mixed $caller
     *   The caller where this was thrown.
     */
    public function __construct($caller)
    {
        parent::__construct(sprintf(
            '%s has not defined any option types. This may indicate that Option::create() should be used instead.',
            Helper::toString($caller)
        ));
    }
}
