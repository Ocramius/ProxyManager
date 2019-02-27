<?php

declare(strict_types=1);

namespace ProxyManager\Proxy;

/**
 * Aggregates AccessInterceptor and ValueHolderInterface, mostly for return type hinting
 */
interface AccessInterceptorValueHolderInterface extends AccessInterceptorInterface, ValueHolderInterface
{
}
