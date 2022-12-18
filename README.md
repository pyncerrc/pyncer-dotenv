# Pyncer Dotenv
A [Dotenv](https://github.com/vlucas/phpdotenv) WriterInterface implementation to write .env values to constants.

## Installation

Install via [Composer](https://getcomposer.org):

```bash
$ composer require pyncer/dotenv
```

## Example

```php
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Pyncer\Dotenv\ConstWriter;

// ...

$repository = RepositoryBuilder::createWithNoAdapters()
    ->addWriter(new ConstWriter('Vendor\\Namespace'))
    ->immutable()
    ->make();

$dotenv = Dotenv::create($repository, getcwd());
$dotenv->load();

// ...

echo \Vendor\Namespace\MY_ENV_VARIABLE;
```
