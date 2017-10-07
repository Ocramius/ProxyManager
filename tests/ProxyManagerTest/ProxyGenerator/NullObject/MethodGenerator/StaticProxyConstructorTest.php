<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\NullObject\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
class StaticProxyConstructorTest extends TestCase
{
    public function testBodyStructure() : void
    {
        $constructor = new StaticProxyConstructor(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertTrue($constructor->isStatic());
        self::assertSame('public', $constructor->getVisibility());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

$instance->publicProperty0 = null;
$instance->publicProperty1 = null;
$instance->publicProperty2 = null;

return $instance;',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithoutPublicProperties() : void
    {
        $constructor = new StaticProxyConstructor(
            new ReflectionClass(ClassWithPrivateProperties::class)
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());
        $body = $constructor->getBody();
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

return $instance;',
            $body
        );
    }
}
