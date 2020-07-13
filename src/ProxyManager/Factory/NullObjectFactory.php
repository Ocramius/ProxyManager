<?php

declare(strict_types=1);

namespace ProxyManager\Factory;

use OutOfBoundsException;
use ProxyManager\Configuration;
use ProxyManager\Proxy\NullObjectInterface;
use ProxyManager\ProxyGenerator\NullObjectGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use ProxyManager\Signature\Exception\MissingSignatureException;

use function get_class;
use function is_object;

/**
 * Factory responsible of producing proxy objects
 */
class NullObjectFactory extends AbstractBaseFactory
{
    private NullObjectGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new NullObjectGenerator();
    }

    /**
     * @param object|string $instanceOrClassName the object to be wrapped or interface to transform to null object
     *
     * @throws InvalidSignatureException
     * @throws MissingSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType of object
     *
     * @psalm-param RealObjectType|class-string<RealObjectType> $instanceOrClassName
     *
     * @psalm-return RealObjectType&NullObjectInterface
     *
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy($instanceOrClassName): NullObjectInterface
    {
        $className      = is_object($instanceOrClassName) ? get_class($instanceOrClassName) : $instanceOrClassName;
        $proxyClassName = $this->generateProxy($className);

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design)
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor();
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
