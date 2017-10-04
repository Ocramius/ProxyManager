<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator;

use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerTestAsset\BaseInterface;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator
 * @group Coverage
 */
class AccessInterceptorScopeLocalizerTest extends AbstractProxyGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * {@inheritDoc}
     */
    public function testGeneratesValidCode(string $className) : void
    {
        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isInterface()) {
            // @todo interfaces *may* be proxied by deferring property localization to the constructor (no hardcoding)
            $this->expectException(InvalidProxiedClassException::class);
        }

        parent::testGeneratesValidCode($className);
    }

    public function testWillRejectInterfaces() : void
    {
        $this->expectException(InvalidProxiedClassException::class);

        $this
            ->getProxyGenerator()
            ->generate(new \ReflectionClass(BaseInterface::class), new ClassGenerator());
    }

    /**
     * {@inheritDoc}
     */
    protected function getProxyGenerator() : ProxyGeneratorInterface
    {
        return new AccessInterceptorScopeLocalizerGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces() : array
    {
        return [AccessInterceptorInterface::class];
    }
}
