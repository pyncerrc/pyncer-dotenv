# Pyncer Dotenv
A [phpdotenv](https://github.com/vlucas/phpdotenv) implementation to read and write .env values from and to constants.

## Installation

Install via [Composer](https://getcomposer.org):

```bash
$ composer require pyncer/dotenv
```

## Example

```php
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Pyncer\Dotenv\ConstAdapter;

// ...

$repository = RepositoryBuilder::createWithNoAdapters()
    ->addAdapter(new ConstAdapter('Vendor\\Namespace'))
    ->immutable()
    ->make();

$dotenv = Dotenv::create($repository, getcwd());
$dotenv->load();

// ...

echo \Vendor\Namespace\MY_ENV_VARIABLE;
```
