<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
class StaticProxyConstructorTest extends TestCase
{
    /**
     * @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $prefixInterceptors;

    /**
     * @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $suffixInterceptors;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $this->prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $this->suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));
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

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

$instance->bindProxyProperties($localizedObject, $prefixInterceptors, $suffixInterceptors);

return $instance;',
            $method->getBody()
        );
    }
}
