<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

/**
 * Simple interface for a remote API that methods with default parameters
 *
 * @author Pedro Tanaka <pedro.stanaka@gmail.com>
 * @license MIT
 */
interface RemoteServiceWithDefaultsInterface
{
    public function optionalNonNullable(string $foo, string $optional = ''): int;

    public function optionalNullable(string $foo, ?float $nullable = null): int;
}
