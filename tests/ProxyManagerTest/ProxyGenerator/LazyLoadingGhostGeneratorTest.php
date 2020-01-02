<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator}
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator
 * @group Coverage
 */
final class LazyLoadingGhostGeneratorTest extends AbstractProxyGeneratorTest
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
            ->generate(new ReflectionClass(BaseInterface::class), new ClassGenerator());
    }

    public function testAllAbstractMethodsWillBeMadeConcrete() : void
    {
        $classGenerator = new ClassGenerator();

        $this
            ->getProxyGenerator()
            ->generate(new ReflectionClass(ClassWithAbstractPublicMethod::class), $classGenerator);

        foreach ($classGenerator->getMethods() as $method) {
            self::assertFalse($method->isAbstract());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getProxyGenerator() : ProxyGeneratorInterface
    {
        return new LazyLoadingGhostGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces() : array
    {
        return [GhostObjectInterface::class];
    }
}
