<?php

declare(strict_types=1);

namespace ProxyManager\Factory;

use Closure;
use OutOfBoundsException;
use ProxyManager\Configuration;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use ProxyManager\Signature\Exception\MissingSignatureException;

/**
 * Factory responsible of producing ghost instances
 */
class LazyLoadingGhostFactory extends AbstractBaseFactory
{
    private LazyLoadingGhostGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new LazyLoadingGhostGenerator();
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }

    /**
     * Creates a new lazy proxy instance of the given class with
     * the given initializer
     *
     * Please refer to the following documentation when using this method:
     *
     * @link https://github.com/Ocramius/ProxyManager/blob/master/docs/lazy-loading-ghost-object.md
     *
     * @param string  $className    name of the class to be proxied
     * @param Closure $initializer  initializer to be passed to the proxy. The initializer closure should have following
     *                              signature:
     *
     *                              <code>
     *                              $initializer = function (
     *                                  GhostObjectInterface $proxy,
     *                                  string $method,
     *                                  array $parameters,
     *                                  & $initializer,
     *                                  array $properties
     *                              ) {};
     *                              </code>
     *
     *                              Where:
     *                               - $proxy is the proxy instance on which the initializer is acting
     *                               - $method is the name of the method that triggered the lazy initialization
     *                               - $parameters are the parameters that were passed to $method
     *                               - $initializer by-ref initializer - should be assigned null in the initializer body
     *                               - $properties a by-ref map of the properties of the object, indexed by PHP
     *                                             internal property name. Assign values to it to initialize the
     *                                             object state
     * @param mixed[] $proxyOptions a set of options to be used when generating the proxy. Currently supports only
     *                              key "skippedProperties", which allows to skip lazy-loading of some properties.
     *                              "skippedProperties" is a string[], containing a list of properties referenced
     *                              via PHP's internal property name (i.e. "\0ClassName\0propertyName")
     *
     * @throws MissingSignatureException
     * @throws InvalidSignatureException
     * @throws OutOfBoundsException
     *
     * @psalm-template RealObjectType as object
     *
     * @psalm-param class-string<RealObjectType> $className
     * @psalm-param Closure(
     *   RealObjectType&GhostObjectInterface<RealObjectType>=,
     *   string=,
     *   array<string, mixed>=,
     *   ?Closure=,
     *   array<string, mixed>=
     * ) : bool $initializer
     * @psalm-param array{skippedProperties?: array<int, string>} $proxyOptions
     *
     * @psalm-return RealObjectType&GhostObjectInterface<RealObjectType>
     *
     * @psalm-suppress MixedInferredReturnType We ignore type checks here, since `staticProxyConstructor` is not
     *                                         interfaced (by design)
     */
    public function createProxy(
        string $className,
        Closure $initializer,
        array $proxyOptions = []
    ): GhostObjectInterface {
        $proxyClassName = $this->generateProxy($className, $proxyOptions);

        /**
         * We ignore type checks here, since `staticProxyConstructor` is not interfaced (by design)
         *
         * @psalm-suppress MixedMethodCall
         * @psalm-suppress MixedReturnStatement
         */
        return $proxyClassName::staticProxyConstructor($initializer);
    }
}
