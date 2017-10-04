<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator;

use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator
 * @group Coverage
 */
class LazyLoadingValueHolderGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * {@inheritDoc}
     */
    protected function getProxyGenerator() : ProxyGeneratorInterface
    {
        return new LazyLoadingValueHolderGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces() : array
    {
        return [VirtualProxyInterface::class];
    }
}
