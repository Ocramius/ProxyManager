<?php

declare(strict_types=1);

namespace ProxyManager\Factory;

use OutOfBoundsException;
use ProxyManager\Configuration;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Proxy\RemoteObjectInterface;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\ProxyGenerator\RemoteObjectGenerator;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use ProxyManager\Signature\Exception\MissingSignatureException;
use function get_class;
use function is_object;

/**
 * Factory responsible of producing remote proxy objects
 */
class RemoteObjectFactory extends AbstractBaseFactory
{
    /** @var AdapterInterface */
    protected $adapter;

    /** @var RemoteObjectGenerator|null */
    private $generator;

    /**
     * {@inheritDoc}
     *
     * @param AdapterInterface $adapter
     * @param Configuration    $configuration
     */
    public function __construct(AdapterInterface $adapter, ?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->adapter = $adapter;
    }

    /**
     * @param string|object $instanceOrClassName
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     */
    public function createProxy($instanceOrClassName) : RemoteObjectInterface
    {
        $proxyClassName = $this->generateProxy(
            is_object($instanceOrClassName) ? get_class($instanceOrClassName) : $instanceOrClassName
        );

        return $proxyClassName::staticProxyConstructor($this->adapter);
    }

    /**
     * {@inheritDoc}
     */
    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator ?: $this->generator = new RemoteObjectGenerator();
    }
}
