<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with pre-existing magic methods
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMagicMethods
{
    public function __set($name, $value)
    {
        return [$name => $value];
    }

    public function __get($name)
    {
        return $name;
    }

    public function __isset($name)
    {
        return (bool) $name;
    }

    public function __unset($name)
    {
        return (bool) $name;
    }

    public function __sleep()
    {
        return [];
    }

    public function __wakeup()
    {
    }

    public function __clone()
    {
    }
}
