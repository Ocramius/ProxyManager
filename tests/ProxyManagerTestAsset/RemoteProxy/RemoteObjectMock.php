<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

use ProxyManager\Proxy\RemoteObjectInterface;

/**
 * Simple remote object mock implementation
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class RemoteObjectMock implements RemoteObjectInterface
{
    /**
     * @return static
     */
    public static function staticProxyConstructor() : self
    {
        return new static();
    }
}
