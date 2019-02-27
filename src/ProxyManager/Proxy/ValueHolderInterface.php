<?php

declare(strict_types=1);

namespace ProxyManager\Proxy;

/**
 * Value holder marker
 */
interface ValueHolderInterface extends ProxyInterface
{
    /**
     * @return object|null the wrapped value
     */
    public function getWrappedValueHolderValue() : ?object;
}
