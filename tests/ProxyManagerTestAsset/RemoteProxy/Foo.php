<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

/**
 * Simple interface for a remote API
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Foo implements FooServiceInterface, BazServiceInterface
{
    /**
     * @return string
     */
    public function foo()
    {
        return 'bar remote';
    }

    /**
     * @param string $param
     *
     * @return string
     */
    public function baz($param)
    {
        return $param . ' remote';
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {
        return $name . ' remote';
    }
}
