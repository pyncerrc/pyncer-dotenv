<?php
namespace Pyncer\Dotenv;

use Dotenv\Repository\Adapter\WriterInterface;
use Pyncer\Dotenv\ConstWriterTrait;

final class ConstWriter implements WriterInterface
{
    use ConstWriterTrait;

    public function __construct(string $namespace = '')
    {
        $this->setNamespace($namespace);
    }
}
