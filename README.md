# unicorn-fail/php-option

> A highly extensible replacement for [phpoption/phpoption] with `TypedOption` support.

[![Latest Version](https://img.shields.io/packagist/v/unicorn-fail/php-option.svg?style=flat-square)](https://packagist.org/packages/unicorn-fail/php-option)
[![Total Downloads](https://img.shields.io/packagist/dt/unicorn-fail/php-option.svg?style=flat-square&color=blue)](https://packagist.org/packages/unicorn-fail/php-option)
[![License](https://img.shields.io/github/license/unicorn-fail/php-option?color=blue&style=flat-square)](LICENSE)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/unicorn-fail/php-option?style=flat-square)](https://travis-ci.com/unicorn-fail/php-option)

[![Build Status](https://img.shields.io/travis/com/unicorn-fail/php-option.svg?style=flat-square)](https://travis-ci.com/unicorn-fail/php-option)
[![Codacy grade](https://img.shields.io/codacy/grade/39609995560840e282fd401b6ce91b4f?style=flat-square)](https://www.codacy.com/app/unicorn-fail/php-option?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=unicorn-fail/php-option&amp;utm_campaign=Badge_Grade)
[![Codacy coverage](https://img.shields.io/codacy/coverage/39609995560840e282fd401b6ce91b4f?style=flat-square)](https://www.codacy.com/app/unicorn-fail/php-option?utm_source=github.com&utm_medium=referral&utm_content=unicorn-fail/php-option&utm_campaign=Badge_Coverage)
![phpcs coding standard](https://img.shields.io/badge/phpcs-PSR2-brightgreen?style=flat-square)

An `Option` is intended for cases where you sometimes might return a value
(typically an object), and sometimes you might return no value (typically null)
depending on arguments, or other runtime factors.

Often times, you forget to handle the case where no value is returned. Not intentionally
of course, but maybe you did not account for all possible states of the system; or maybe you
indeed covered all cases, then time goes on, code is refactored, some of these your checks 
might become invalid, or incomplete. Suddenly, without noticing, the no value case is not
handled anymore. As a result, you might sometimes get fatal PHP errors telling you that 
you called a method on a non-object; users might see blank pages, or worse.

On one hand, an `Option` forces a developer to consciously think about both cases
(returning a value, or returning no value). That in itself will already make your code more
robust. On the other hand, the `Option` also allows the API developer to provide
more concise API methods, and empowers the API user in how he consumes these methods.



## 📦 Installation

This project can be installed via [Composer]:

``` bash
$ composer require unicorn-fail/php-option
```

## Basic Usage

### Using `Option` in your API

```php
<?php

use UnicornFail\PhpOption\None;
use UnicornFail\PhpOption\Some;

class MyRepository
{
    public function findSomeEntity($criteria)
    {
        if (null !== $entity = $this->entityManager->find($criteria)) {
            return Some::create($entity);
        }

        // Use a singleton for the None case (it's statically cached for performance).
        return None::create();
    }
}
```

If you are consuming an existing library, you can also use a shorter version
which by default treats ``null`` as ``None``, and everything else as ``Some`` case:

After:
```php
<?php

use UnicornFail\PhpOption\Option;

class MyRepository
{
    public function findSomeEntity($criteria)
    {
        return Option::create($this->entityManager->find($criteria));

        // Or, if you want to change the none value to false for example:
        return Option::create($this->em->find($criteria), ['noneValue' => false]);
    }
}
```

#### Case 1: always require an Entity when invoking code
```php
$entity = $repo->findSomeEntity($criteria)->get(); // Returns an Entity, or throws exception.
```

#### Case 2: fallback to default value if not available
```php
$entity = $repo->findSomeEntity($criteria)->getOrElse(new Entity);

// Or, if you need to lazily create the entity.
$entity = $repo->findSomeEntity($criteria)->getOrCall(function() {
    return new Entity;
});
```

### No More Boiler Plate Code

Before:
```php
$entity = $this->findSomeEntity();
if ($entity === null) {
    throw new NotFoundException();
}
return $entity->name;
```

After:
```php
return $this->findSomeEntity()->get()->name;
```


### No more control flow exceptions

Before:
```php
try {
    $entity = $this->findSomeEntity();
} catch (NotFoundException $ex) {
    $entity = new Entity;
}
```

After:
```php
$entity = $this->findSomeEntity()->getOrElse(new Entity);
```

### Concise null handling

Before:
```php
$entity = $this->findSomeEntity();
if ($entity === null) {
    return new Entity;
}

return $entity;
```

After:
```php
return $this->findSomeEntity()->getOrElse(new Entity);
```

### Chaining multiple alternative Options

If you need to try multiple alternatives, the ``orElse`` method allows you to
do this very elegantly.

Before:
```php
$entity = $this->findSomeEntity();
if ($entity === null) {
    $entity = $this->findSomeOtherEntity();
    if ($entity === null) {
        $entity = $this->createEntity();
    }
}
return $entity;
```

After:
```php
return $this->findSomeEntity()
            ->orElse($this->findSomeOtherEntity())
            ->orElse($this->createEntity());
```

The first option which is non-empty will be returned. This is especially useful 
with lazily evaluated options.

### Lazily evaluated Options

The above example has a flaw where the option chain would need to evaluate all
options when the method is called. This creates unnecessary overhead if the first
option is already non-empty.

Fortunately, this can be easily solved by using `LazyOption` which takes a callable
that will be invoked only if necessary:

```php
use UnicornFail\PhpOption\LazyOption;

return $this->findSomeEntity()
            ->orElse(LazyOption::create([$this, 'findSomeOtherEntity']))
            ->orElse(LazyOption::create([$this, 'createEntity']));
```

### Typed options

In cases where you need a specific PHP type returned (e.g. string, boolean, number, etc.) the `TypedOption` class
may provide you with more flexibility:

Before:
```php
// ?coords=32:43,35:22,94:33,95:34
$coordsStr = !!(isset($_GET['coords']) ? $_GET['coords'] : '');
$coords = $coordsStr ? array_map('trim', explode(',', $coordsStr)) : [];
foreach ($coords as $coord) {
    list ($x, $y) = array_map('trim', explode(':', $coord));
    $this->doSomething($x, $y);
}
```

After:
```php
use UnicornFail\PhpOption\TypedOption;

// Automatically parsed by the SomeArray typed option.
// ?coords=32:43,35:22,94:33,95:34
$items = TypedOption::pick($_GET, 'coords', ['keyDelimiter' => ':'])->getOrElse([]);
foreach ($items as $x => $y) {
    $this->doSomething($x, $y);
}
```

## 📓 API Documentation

Official and extensive API documentation coming soon ([PRs are welcome]).

## 🏷️ Versioning

[SemVer](http://semver.org/) is followed closely. Minor and patch releases should not introduce breaking changes to the codebase.

This project's initial release will start at version `1.6.0` to stay in line with existing [phpoption/phpoption] releases.

## 🛠️ Support & Backward Compatibility

### Version `<1.6.0`

- This project will not patch any bugs, address any security related issues, or make another release.
  Please upgrade, it should be as simple as:
  ```bash
  $ composer remove phpoption/phpoption
  $ composer require unicorn-fail/php-option
  ```

### Version `>=1.6.0 <2.0.0`

- This project will keep backward compatibility with [phpoption/phpoption] and continue running the [original tests]
  to ensure previous namespaces and implementation are still functional.
- The following classes are automatically registered as aliases so existing code should still remain functional (see known caveats below):
  - `PhpOption\LazyOption` => `UnicornFail\PhpOption\LazyOption`
  - `PhpOption\None` => `UnicornFail\PhpOption\None`
  - `PhpOption\Option` => `UnicornFail\PhpOption\Option`
  - `PhpOption\Some` => `UnicornFail\PhpOption\Some`
- The following methods have been deprecated, use their replacements instead:
  - `Option::ensure()` => `Option::create()`
  - `Option::fromValue()` => `Option::create()`
  - `Option::fromArraysValue()` => `Option::pick()`
  - `Option::fromReturn()` => `Option::create()`
  - `$option->ifDefined()` => `$option->forAll()`
- Known caveats:
  - The [original tests] contain extremely [minor alterations](PhpOption/tests/changes.patch) due testing/environment issues.
  - PHP 5.3 has a weird bug where class aliases aren't being registered properly. Because of this, the classes had
    to be extended from their respective replacements. This, unfortunately, prevents `\PhpOption\Some`,
    `\PhpOption\None` and `\PhpOption\LazyOption` from being able to be extended directly from `\PhpOption\Option`.
    If your code implements anything resembling `$option instanceof Option`, these will fail. You will need to change
    these to `$option instance of \UnicornFail\PhpOption\OptionInterface` instead.
  
### Version `2.0.0`

- This project plans to remove support for all of `PHP 5`, `PHP 7.0`, `PHP 7.1` and backward compatibility with [phpoption/phpoption].


## ⛔ Security

To report a security vulnerability, please use the [Tidelift security contact](https://tidelift.com/security).
Tidelift will coordinate the fix and disclosure with us.

## 👷‍♀️ Contributing

Contributions to this library are **welcome**!

Please see [CONTRIBUTING](https://github.com/unicorn-fail/php-option/blob/master/.github/CONTRIBUTING.md) for additional details.

## 🧪 Testing

Local development (ignore changes to `composer.json`):
``` bash
$ composer require-test
$ composer test
```

With coverage:
``` bash
$ composer require-test
$ composer require-coverage
$ composer test-coverage
```


## 🚀 Performance Benchmarks

Of course, performance is important. Included in the tests is a
performance benchmark which you can run on a machine of your choosing:

```bash
$ composer test-group performance
```

At its core, the overhead incurred by using `Option` comes down to the time that it takes to
create one object, the `Option` wrapper. It will also need to perform an additional method
call to retrieve the value from the wrapper. Depending on your use case(s) and desired
functionality, you may encounter varied results.

#### Average Overhead Per Invocation*

|                      |      `None::create()`      |      `Some::create()`      |
|:---------------------|:--------------------------:|:--------------------------:|
| [PHP 5.3][php53]     |   [0.000000539s][php53n]   |   [0.000010369s][php53s]   |
| [PHP 5.4][php54]     |   [0.000000427s][php54n]   |   [0.000007331s][php54s]   |
| [PHP 5.5][php55]     |   [0.000000422s][php55n]   |   [0.000007045s][php55s]   |
| [PHP 5.6][php56]     |   [0.000001036s][php56n]   |   [0.000006680s][php56s]   |
| [PHP 7.0][php70]     |   [0.000000185s][php70n]   |   [0.000002357s][php70s]   |
| [PHP 7.1][php71]     |   [0.000000124s][php71n]   |   [0.000002027s][php71s]   |
| [PHP 7.2][php72]     |   [0.000000127s][php72n]   |   [0.000001841s][php72s]   |
| **[PHP 7.3][php73]** | **[0.000000111s][php73n]** | **[0.000001682s][php73s]** |

[php53]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071612#L195-L196
[php53n]: http://www.unitarium.com/time?val=0.000000539&ac=6
[php53s]: http://www.unitarium.com/time?val=0.000010369&ac=6
[php54]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071613#L230-L231
[php54n]: http://www.unitarium.com/time?val=0.000000427&ac=6
[php54s]: http://www.unitarium.com/time?val=0.000007331&ac=6
[php55]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071614#L189-L190
[php55n]: http://www.unitarium.com/time?val=0.000000422&ac=6
[php55s]: http://www.unitarium.com/time?val=0.000007045&ac=6
[php56]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071615#L188-L189
[php56n]: http://www.unitarium.com/time?val=0.000001036&ac=6
[php56s]: http://www.unitarium.com/time?val=0.000006680&ac=6
[php70]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071616#L187-L188
[php70n]: http://www.unitarium.com/time?val=0.000000185&ac=6
[php70s]: http://www.unitarium.com/time?val=0.000002357&ac=6
[php71]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071617#L186-L187
[php71n]: http://www.unitarium.com/time?val=0.000000124&ac=6
[php71s]: http://www.unitarium.com/time?val=0.000002027&ac=6
[php72]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071618#L186-L187
[php72n]: http://www.unitarium.com/time?val=0.000000127&ac=6
[php72s]: http://www.unitarium.com/time?val=0.000001841&ac=6
[php73]: https://travis-ci.com/unicorn-fail/php-option/jobs/223071619#L186-L187
[php73n]: http://www.unitarium.com/time?val=0.000000111&ac=6
[php73s]: http://www.unitarium.com/time?val=0.000001682&ac=6

In the table above, these benchmarks rarely are well under a fraction of a microsecond. Many of them
measure in nanoseconds; with newer PHP versions decreasing the overhead even more over time.

Unless you plan to call a method hundreds of thousands of times during a request, there is no reason
to stick to the `object|null` return value; better give your code some options!

> _*Average based on the comparison of creating a single object vs. the creation of a wrapper and a single
method call; iterated over 10000 times and then calculating the difference._


## 👥 Credits & Acknowledgements

- Mark Carver ([twitter](https://twitter.com/_markcarver)) ([github](https://github.com/markcarver))
- Johannes ([twitter](https://twitter.com/schmittjoh])) ([github](https://github.com/schmittjoh))
- [All Contributors](https://github.com/unicorn-fail/php-option/contributors)

## 📄 License

**unicorn-fail/php-option** is licensed under the Apache 2.0 license.  See the [`LICENSE`](LICENSE) file for more details.

[Composer]: https://getcomposer.org/
[original tests]: PhpOption/tests
[phpoption/phpoption]: https://packagist.org/packages/phpoption/phpoption
[PRs are welcome]: https://github.com/unicorn-fail/php-option/pulls
