<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoading\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor}
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
final class StaticProxyConstructorTest extends TestCase
{
    public function testBodyStructure() : void
    {
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');

        $constructor = new StaticProxyConstructor(
            $initializer,
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertCount(1, $constructor->getParameters());
        self::assertTrue($constructor->isStatic());
        self::assertSame('public', $constructor->getVisibility());

        self::assertStringMatchesFormat(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

unset($instance->publicProperty0, $instance->publicProperty1, $instance->publicProperty2, '
            . '$instance->protectedProperty0, $instance->protectedProperty1, $instance->protectedProperty2);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $instance, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($instance);

$instance->foo = $initializer;

return $instance;',
            $constructor->getBody()
        );
    }
}
