<?php
namespace Pyncer\Dotenv;

use Pyncer\Exception\InvalidArgumentException;
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

trait ConstWriterTrait {
    private $namespace = '';

    protected function setNamespace(string $value): void
    {
        if ($value !== '') {
            $value = rtrim($value, '\\') . '\\';
        }

        $this->namespace = $value;
    }

    /**
     * Write to an environment variable, if possible.
     *
     * @param string $name
     * @param string $value
     *
     * @return bool
     */
    public function write(string $name, string $value)
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                'Name cannot be empty.'
            );
        }

        $fullName = $this->getFullName($name);

        if (defined($fullName)) {
            return false;
        }

        if ($value === 'null') {
            $value = null;
        } elseif ($value === 'false' || $value === '!true') {
            $value = false;
        } elseif ($value === 'true' || $value === '!false') {
            $value = true;
        } elseif (str_starts_with($value, '0') && strval(intval($value)) === substr($value, 1)) {
            $value = octdec($value);
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

        define($fullName, $value);

        return true;
    }

    /**
     * Delete an environment variable, if possible.
     *
     * @param strng $name
     *
     * @return bool
     */
    public function delete(string $name)
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                'Name cannot be empty.'
            );
        }

        return false;
    }

    private function getFullName(string $name): string
    {
        if (str_contains($name, '__')) {
            return str_replace('__', '\\', $name);
        }

        return $this->namespace . $name;
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
