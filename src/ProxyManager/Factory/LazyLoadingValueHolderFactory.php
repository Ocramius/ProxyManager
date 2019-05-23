<?php

declare(strict_types=1);

namespace ProxyManager\Factory;

use Closure;
use ProxyManager\Configuration;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Factory responsible of producing virtual proxy instances
 */
class LazyLoadingValueHolderFactory extends AbstractBaseFactory
{
    private LazyLoadingValueHolderGenerator $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new LazyLoadingValueHolderGenerator();
    }

    /**
     * @param array<string, mixed> $proxyOptions
     *
     * @psalm-template RealObjectType
     *
     * @psalm-param class-string<RealObjectType> $className
     *
     * @psalm-return RealObjectType&VirtualProxyInterface
     */
    public function createProxy(
        string $className,
        Closure $initializer,
        array $proxyOptions = []
    ) : VirtualProxyInterface {
        $proxyClassName = $this->generateProxy($className, $proxyOptions);

        return $proxyClassName::staticProxyConstructor($initializer);
    }

    /**
     * {@inheritDoc}
     */
    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
