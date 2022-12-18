# Pyncer DotEnv
A [DotEnv](https://github.com/vlucas/phpdotenv) WriterInterface implementation to write .env values to constants.

## Example

```php
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Pyncer\DotEnv\ConstWriter;

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
