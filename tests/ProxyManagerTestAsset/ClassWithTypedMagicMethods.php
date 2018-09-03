<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test class used to verify that proxy-manager respects typed magic methods
 */
class ClassWithTypedMagicMethods
{
    public function __set(int $name, bool $value) : void
    {
    }

    public function __get(int $name) : bool
    {
        return false;
    }

    public function __isset(int $name) : object
    {
        return $this;
    }

    public function __unset(int $name) : object
    {
        return $this;
    }

    public function __sleep() : int
    {
        return 123;
    }

    public function __wakeup() : int
    {
    }
}
