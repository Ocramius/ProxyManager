<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use ProxyManager\Proxy\NullObjectInterface;

/**
 * Base test class to catch instantiations of null object
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class NullObjectMock implements NullObjectInterface
{
    /**
     * @return static
     */
    public static function staticProxyConstructor() : self
    {
        return new static();
    }
}
