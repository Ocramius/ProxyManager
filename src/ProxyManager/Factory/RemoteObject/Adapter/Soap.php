<?php

declare(strict_types=1);

namespace ProxyManager\Factory\RemoteObject\Adapter;

/**
 * Remote Object SOAP adapter
 */
class Soap extends BaseAdapter
{
    /**
     * {@inheritDoc}
     */
    protected function getServiceName(string $wrappedClass, string $method) : string
    {
        return $method;
    }
}
