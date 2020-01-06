<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

/**
 * Simple interface for a remote API that methods with default parameters
 */
interface RemoteServiceWithDefaultsInterface
{
    public function optionalNonNullable(string $foo, string $optional = 'Optional parameter to be kept during calls') : int;

    public function manyRequiredWithManyOptional(
        string $required,
        int $requiredInt,
        string $optional = 'Optional parameter to be kept during calls',
        int $optionalInt = 100,
        string $optionalStr = 'Yet another optional parameter to be kept during calls'
    );

    public function optionalNullable(string $foo, ?float $nullable = null) : int;
}
