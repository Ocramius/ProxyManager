<?php

declare(strict_types=1);

namespace ProxyManager\Factory\RemoteObject\Adapter;

use ProxyManager\Factory\RemoteObject\AdapterInterface;
use Zend\Server\Client;
use function array_key_exists;

/**
 * Remote Object base adapter
 */
abstract class BaseAdapter implements AdapterInterface
{
    /**
     * Adapter client
     *
     * @var Client
     */
    protected $client;

    /**
     * Service name mapping
     *
     * @var string[]
     */
    protected $map = [];

    /**
     * Constructor
     *
     * @param string[] $map map of service names to their aliases
     */
    public function __construct(Client $client, array $map = [])
    {
        $this->client = $client;
        $this->map    = $map;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed[] $params
     */
    public function call(string $wrappedClass, string $method, array $params = [])
    {
        $serviceName = $this->getServiceName($wrappedClass, $method);

        if (array_key_exists($serviceName, $this->map)) {
            $serviceName = $this->map[$serviceName];
        }

        return $this->client->call($serviceName, $params);
    }

    /**
     * Get the service name will be used by the adapter
     */
    abstract protected function getServiceName(string $wrappedClass, string $method) : string;
}
