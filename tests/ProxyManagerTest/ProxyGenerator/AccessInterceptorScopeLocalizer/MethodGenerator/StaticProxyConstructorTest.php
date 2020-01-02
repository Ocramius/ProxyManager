<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor}
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
final class StaticProxyConstructorTest extends TestCase
{
    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $prefixInterceptors;

    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $suffixInterceptors;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $this->prefixInterceptors->method('getName')->willReturn('pre');
        $this->suffixInterceptors->method('getName')->willReturn('post');
    }

    public function testSignature() : void
    {
        $method = new StaticProxyConstructor(new ReflectionClass(ClassWithProtectedProperties::class));

        self::assertSame('staticProxyConstructor', $method->getName());
        self::assertTrue($method->isStatic());
        self::assertSame('public', $method->getVisibility());

        $parameters = $method->getParameters();

        self::assertCount(3, $parameters);

        self::assertSame(ClassWithProtectedProperties::class, $parameters['localizedObject']->getType());
        self::assertSame('array', $parameters['prefixInterceptors']->getType());
        self::assertSame('array', $parameters['suffixInterceptors']->getType());
    }

    public function testBodyStructure() : void
    {
        $method = new StaticProxyConstructor(new ReflectionClass(ClassWithPublicProperties::class));

        self::assertSame(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

$instance->bindProxyProperties($localizedObject, $prefixInterceptors, $suffixInterceptors);

return $instance;',
            $method->getBody()
        );
    }
}
