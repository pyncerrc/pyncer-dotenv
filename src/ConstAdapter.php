<?php
namespace Pyncer\Dotenv;

use Dotenv\Repository\Adapter\AdapterInterface;
use Pyncer\Dotenv\ConstWriterTrait;
use Pyncer\Exception\InvalidArgumentException;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

use function constant;
use function defined;

final class ConstAdapter implements AdapterInterface
{
    use ConstWriterTrait;

    public function __construct(string $namespace = '')
    {
        $this->setNamespace($namespace);
    }

    /**
     * Create a new instance of the adapter, if it is available.
     *
     * @return \PhpOption\Option<\Dotenv\Repository\Adapter\AdapterInterface>
     */
    public static function create()
    {
        /** @var \PhpOption\Option<AdapterInterface> */
        return Some::create(new self());
    }

    /**
     * Read an environment variable, if it exists.
     *
     * @param string $name
     *
     * @return \PhpOption\Option<string>
     */
    public function read(string $name)
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                'Name cannot be empty.'
            );
        }

        if (defined($this->namespace . $name)) {
            $value = constant($this->namespace . $name);

            if ($value === null) {
                $value = 'null';
            } elseif ($value === true) {
                $value = 'true';
            } elseif ($value === false) {
                $value = 'false';
            } elseif (is_array($value)) {
                $value = $this->buildArray($value);
            } else {
                $value = strval($value);
            }

            return Option::fromValue($value);
        }

        return None::create();
    }

    private function buildArray(array $array): string
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->buildArray($array);
            }
        }

        if (array_is_list($array)) {
            return '[' . implode(',', $array) . ']';
        }

        $parts = [];

        foreach ($array as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        return '[' . implode(',', $parts) . ']';
    }
}
