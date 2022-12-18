<?php
namespace Pyncer\DotEnv;

use Dotenv\Repository\Adapter\WriterInterface;
use Pyncer\Exception\UnexpectedValueException;

use function array_map;
use function define;
use function defined;
use function explode;
use function intval;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function strval;
use function substr;
use function trim;

final class ConstWriter implements WriterInterface
{
    private $namespace;

    public function __construct(
        string $namespace = ''
    ) {
        if ($namespace !== '') {
            $this->namespace = rtrim($namespace, '\\') . '\\';
        } else {
            $this->namespace = '';
        }
    }

    /**
     * Write to an environment variable, if possible.
     *
     * @param non-empty-string $name
     * @param string           $value
     *
     * @return bool
     */
    public function write(string $name, string $value)
    {
        if (defined($this->namespace . $name)) {
            return false;
        }

        if ($value === 'null') {
            $value = null;
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'true') {
            $value = true;
        } elseif (strval(intval($value)) === $value) {
            $value = intval($value);
        } elseif (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            if (str_contains($value, '=')) {
                $value = $this->parseArray($value);
            } else {
                $value = $this->parseArrayValue($value);
            }
            if ($value === null) {
                throw new UnexpectedValueException(
                    'Invalid .env array value. (' . $name . ')'
                );
            }
        }

        define($this->namespace . $name, $value);

        return true;
    }

    /**
     * Delete an environment variable, if possible.
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    public function delete(string $name)
    {
        return false;
    }

    private function parseArray(string $array): ?array
    {
        $array = substr($array, 1, -1);
        $len = strlen($array);

        $result = [];

        $key = null;
        $value = null;
        $pos = 0;
        $depth = 0;
        $inValue = false;

        for ($i = 0; $i < $len; ++$i) {
            $char = substr($array, $i, 1);

            if ($char === '=' && !$inValue) {
                $key = trim(substr($array, $pos, $i - $pos));
                $inValue = true;
                $pos = $i + 1;
                continue;
            }

            if ($char === '[' && $inValue) {
                ++$depth;
                continue;
            }

            if ($char === ']' && $inValue) {
                --$depth;
                if ($depth < 0) {
                    return null;
                }
                continue;
            }

            if ($char === ',' && $inValue && $depth === 0) {
                $value = trim(substr($array, $pos, $i - $pos));
                $value = $this->parseArrayValue($value);

                if ($value === null) {
                    return null;
                }

                $inValue = false;
                $pos = $i + 1;
                $result[$key] = $value;
                continue;
            }
        }

        if ($inValue && $depth === 0) {
            $value = trim(substr($array, $pos));
            $value = $this->parseArrayValue($value);

            if ($value === null) {
                return null;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    private function parseArrayValue(string $value): null|array|string
    {
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            if (str_contains($value, '=')) {
                $value = $this->parseArray($value);
                if ($value === null) {
                    return null;
                }
            } else {
                $value = trim(substr($value, 1, -1));
                $value = explode(',', $value);
                $value = array_map('trim', $value);
            }
        }

        return $value;
    }
}
