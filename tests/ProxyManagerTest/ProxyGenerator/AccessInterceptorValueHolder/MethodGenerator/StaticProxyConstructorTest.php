<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor}
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
final class StaticProxyConstructorTest extends TestCase
{
    public function testBodyStructure() : void
    {
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(
                ClassWithTwoPublicProperties::class
            ),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertTrue($constructor->isStatic());
        self::assertSame('public', $constructor->getVisibility());
        self::assertCount(3, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

unset($instance->bar, $instance->baz);

$instance->foo = $wrappedObject;
$instance->pre = $prefixInterceptors;
$instance->post = $suffixInterceptors;

return $instance;',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithoutPublicProperties() : void
    {
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(EmptyClass::class),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

$instance->foo = $wrappedObject;
$instance->pre = $prefixInterceptors;
$instance->post = $suffixInterceptors;

return $instance;',
            $constructor->getBody()
        );
    }

    /**
     * @group 276
     */
    public function testUnsetsPrivatePropertiesAsWell() : void
    {
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(ClassWithMixedProperties::class),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertStringContainsString(
            'unset($instance->publicProperty0, $instance->publicProperty1, $instance->publicProperty2, '
            . '$instance->protectedProperty0, $instance->protectedProperty1, $instance->protectedProperty2);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $instance, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($instance);',
            $constructor->getBody()
        );
    }
}
