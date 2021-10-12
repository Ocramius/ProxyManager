<?php

declare(strict_types=1);

namespace ProxyManager\Factory\RemoteObject\Adapter;

use Laminas\Server\Client;
use ProxyManager\Factory\RemoteObject\AdapterInterface;

use function array_key_exists;

/**
 * Remote Object base adapter
 */
abstract class BaseAdapter implements AdapterInterface
{
    /**
     * Constructor
     *
     * @param array<string, string> $map map of service names to their aliases
     */
    public function __construct(
        protected Client $client,
        // Service name mapping
        protected array $map = []
    ) {
    }

    /**
     * {@inheritDoc}
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
    abstract protected function getServiceName(string $wrappedClass, string $method): string;
}
