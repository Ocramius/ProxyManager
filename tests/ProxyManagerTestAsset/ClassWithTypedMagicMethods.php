<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with pre-existing typed magic methods
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithTypedMagicMethods
{
    public array $data = [];

    public function __set(string|int $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function __get(string|int $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __isset(string|int $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __unset(string|int $name): void
    {
        unset($this->data[$name]);
    }

    public function __sleep(): array
    {
        return ['data'];
    }

    public function __wakeup(): void
    {
    }

    public function __clone(): void
    {
    }
}
