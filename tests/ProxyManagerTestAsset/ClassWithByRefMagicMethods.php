<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test class used to verify that proxy-manager respects magic getters with a byref return value
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithByRefMagicMethods
{
    public function & __set($name, $value)
    {
        return [$name => $value];
    }

    public function & __get($name)
    {
        return $name;
    }

    public function & __isset($name)
    {
        return (bool) $name;
    }

    public function & __unset($name)
    {
        return (bool) $name;
    }

    public function & __sleep()
    {
    }

    public function & __wakeup()
    {
    }
}
