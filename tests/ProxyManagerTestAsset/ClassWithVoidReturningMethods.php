<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test class used to verify that proxy-manager respects void return types on magic methods
 */
class ClassWithVoidReturningMethods
{
    public function __set($name, $value) : void
    {
    }

    public function __get($name) : void
    {
    }

    public function __isset($name) : void
    {
    }

    public function __unset($name) : void
    {
    }

    public function __sleep() : void
    {
    }

    public function __wakeup() : void
    {
    }
}
