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
    /**
     * {@inheritDoc}
     */
    public function & __set($name, $value)
    {
        return [$name => $value];
    }

    /**
     * {@inheritDoc}
     */
    public function & __get($name)
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function & __isset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function & __unset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function & __sleep()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function & __wakeup()
    {
    }
}
