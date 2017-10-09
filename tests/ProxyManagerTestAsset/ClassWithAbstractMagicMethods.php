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
    abstract public function __set($name, $value);

    abstract public function __get($name);

    abstract public function __isset($name);

    abstract public function __unset($name);

    abstract public function __sleep();

    abstract public function __wakeup();

    abstract public function __clone();
}
