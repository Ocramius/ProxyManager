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
    final public function __construct()
    {
    }

    final public function __set($name, $value)
    {
        return [$name => $value];
    }

    final public function __get($name)
    {
        return $name;
    }

    final public function __isset($name)
    {
        return (bool) $name;
    }

    final public function __unset($name)
    {
        return (bool) $name;
    }

    final public function __sleep()
    {
    }

    final public function __wakeup()
    {
    }

    final public function __clone()
    {
    }
}
