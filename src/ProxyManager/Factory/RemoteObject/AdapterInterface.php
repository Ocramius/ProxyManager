<?php

declare(strict_types=1);

namespace ProxyManager\Factory\RemoteObject;

/**
 * Remote Object adapter interface
 */
interface AdapterInterface
{
    /**
     * Call remote object
     *
     * @param array<int, mixed> $params
     *
     * @return mixed
     */
    public function call(string $wrappedClass, string $method, array $params = []);
}
