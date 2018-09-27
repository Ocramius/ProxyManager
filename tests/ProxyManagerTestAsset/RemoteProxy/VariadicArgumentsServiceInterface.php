<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

/**
 * Simple interface for a remote API that has a variadic number of arguments
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface VariadicArgumentsServiceInterface
{
    public function method(string $param1, int ...$param2) : bool;
}
