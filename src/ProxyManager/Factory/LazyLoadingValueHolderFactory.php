<?php

declare(strict_types=1);

namespace ProxyManager\Factory;

use Closure;
use ProxyManager\Configuration;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Proxy\ValueHolderInterface;

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
     * @psalm-template RealObjectType of object
     *
     * @psalm-param class-string<RealObjectType> $className
     *
     * @disabled-psalm-return RealObjectType&VirtualProxyInterface&ValueHolderInterface<RealObjectType>
     * @psalm-return RealObjectType&ValueHolderInterface<RealObjectType>&VirtualProxyInterface
     * @disabled-psalm-return \Foo\MyProxiedClass&VirtualProxyInterface&ValueHolderInterface<\Foo\MyProxiedClass>
     * @disabled-psalm-return \Foo\MyProxiedClass&ValueHolderInterface<\Foo\MyProxiedClass>
     * @disabled-psalm-return \Foo\MyProxiedClass&ValueHolderInterface<\Foo\MyProxiedClass>&VirtualProxyInterface
     * @disabled-psalm-return \Foo\MyProxiedClass&VirtualProxyInterface&ValueHolderInterface<\Foo\MyProxiedClass>
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
