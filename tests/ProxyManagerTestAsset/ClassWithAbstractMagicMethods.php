<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with all existing abstract magic methods
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class ClassWithAbstractMagicMethods
{
    /**
     * {@inheritDoc}
     */
    abstract public function __set($name, $value);

    /**
     * {@inheritDoc}
     */
    abstract public function __get($name);

    /**
     * {@inheritDoc}
     */
    abstract public function __isset($name);

    /**
     * {@inheritDoc}
     */
    abstract public function __unset($name);

    /**
     * {@inheritDoc}
     */
    abstract public function __sleep();

    /**
     * {@inheritDoc}
     */
    abstract public function __wakeup();

    /**
     * {@inheritDoc}
     */
    abstract public function __clone();
}
