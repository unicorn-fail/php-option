<?php

namespace PhpOption;

// Due to some weird bug in PHP 5.3, class_alias() doesn't work
// as expected. Instead, it unfortunately has to be extended.
if (PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION === 3) {
    class Some extends \UnicornFail\PhpOption\Some
    {
    }
} else {
    class_alias('\\UnicornFail\\PhpOption\\Some', '\\PhpOption\\Some');
}
