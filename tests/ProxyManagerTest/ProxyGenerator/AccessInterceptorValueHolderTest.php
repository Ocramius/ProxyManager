<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator;

use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\Proxy\AccessInterceptorValueHolderInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator
 * @group Coverage
 */
class AccessInterceptorValueHolderTest extends AbstractProxyGeneratorTest
{
    /**
     * {@inheritDoc}
     */
    protected function getProxyGenerator() : ProxyGeneratorInterface
    {
        return new AccessInterceptorValueHolderGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces() : array
    {
        return [
            AccessInterceptorValueHolderInterface::class,
            AccessInterceptorInterface::class,
            ValueHolderInterface::class,
        ];
    }
}
