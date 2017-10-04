<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with final pre-existing magic methods
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class ClassWithFinalMagicMethods
{
    /**
     * {@inheritDoc}
     */
    final public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    final public function __set($name, $value)
    {
        return [$name => $value];
    }

    /**
     * {@inheritDoc}
     */
    final public function __get($name)
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    final public function __isset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    final public function __unset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    final public function __sleep()
    {
    }

    /**
     * {@inheritDoc}
     */
    final public function __wakeup()
    {
    }

    /**
     * {@inheritDoc}
     */
    final public function __clone()
    {
    }
}
