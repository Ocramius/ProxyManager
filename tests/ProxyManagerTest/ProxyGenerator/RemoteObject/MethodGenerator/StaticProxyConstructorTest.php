<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\StaticProxyConstructor}
 *
 * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
final class StaticProxyConstructorTest extends TestCase
{
    public function testBodyStructure() : void
    {
        $adapter = $this->createMock(PropertyGenerator::class);

        $adapter->method('getName')->willReturn('adapter');

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(ClassWithMixedProperties::class),
            $adapter
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertTrue($constructor->isStatic());
        self::assertSame('public', $constructor->getVisibility());
        self::assertCount(1, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?? new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

$instance->adapter = $adapter;

unset($instance->publicProperty0, $instance->publicProperty1, $instance->publicProperty2, '
            . '$instance->protectedProperty0, $instance->protectedProperty1, $instance->protectedProperty2);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $instance, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($instance);



return $instance;',
            $constructor->getBody()
        );
    }
}
