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
    /**
     * {@inheritDoc}
     */
    public function __set($name, $value)
    {
        return [$name => $value];
    }

    /**
     * {@inheritDoc}
     */
    public function __get($name)
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __isset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __unset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function __wakeup()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
    }
}
