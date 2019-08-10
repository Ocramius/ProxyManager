<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Assertion;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerTestAsset\AccessInterceptorValueHolderMock;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\CallableTypeHintClass;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerTestAsset\ClassWithFinalMethods;
use ProxyManagerTestAsset\ClassWithMethodWithDefaultParameters;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\FinalClass;
use ProxyManagerTestAsset\HydratedObject;
use ProxyManagerTestAsset\LazyLoadingMock;
use ProxyManagerTestAsset\NullObjectMock;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion}
 *
 * @covers \ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion
 * @group Coverage
 */
final class CanProxyAssertionTest extends TestCase
{
    public function testDeniesFinalClasses() : void
    {
        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(FinalClass::class));
    }

    public function testDeniesClassesWithAbstractProtectedMethods() : void
    {
        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            ClassWithAbstractProtectedMethod::class
        ));
    }

    public function testAllowsInterfaceByDefault() : void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            BaseInterface::class
        ));

        self::assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDeniesInterfaceIfSpecified() : void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseClass::class), false);

        $this->expectException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseInterface::class), false);
    }

    /**
     * @dataProvider validClasses
     * @psalm-param class-string $className
     */
    public function testAllowedClass(string $className) : void
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass($className));

        self::assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDisallowsConstructor() : void
    {
        $this->expectException(BadMethodCallException::class);

        new CanProxyAssertion();
    }

    /**
     * @return string[][]
     */
    public function validClasses() : array
    {
        return [
            [AccessInterceptorValueHolderMock::class],
            [BaseClass::class],
            [BaseInterface::class],
            [CallableTypeHintClass::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithFinalMagicMethods::class],
            [ClassWithFinalMethods::class],
            [ClassWithMethodWithDefaultParameters::class],
            [ClassWithMixedProperties::class],
            [ClassWithPrivateProperties::class],
            [ClassWithProtectedProperties::class],
            [ClassWithPublicProperties::class],
            [ClassWithPublicArrayProperty::class],
            [ClassWithSelfHint::class],
            [ClassWithParentHint::class],
            [EmptyClass::class],
            [HydratedObject::class],
            [LazyLoadingMock::class],
            [NullObjectMock::class],
        ];
    }
}
