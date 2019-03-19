<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithProtectedMethod;
use ProxyManagerTestAsset\FinalClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\Exception\InvalidProxiedClassException}
 *
 * @covers \ProxyManager\Exception\InvalidProxiedClassException
 * @group Coverage
 */
final class InvalidProxiedClassExceptionTest extends TestCase
{
    public function testInterfaceNotSupported() : void
    {
        self::assertSame(
            'Provided interface "ProxyManagerTestAsset\BaseInterface" cannot be proxied',
            InvalidProxiedClassException::interfaceNotSupported(
                new ReflectionClass(BaseInterface::class)
            )->getMessage()
        );
    }

    public function testFinalClassNotSupported() : void
    {
        self::assertSame(
            'Provided class "ProxyManagerTestAsset\FinalClass" is final and cannot be proxied',
            InvalidProxiedClassException::finalClassNotSupported(
                new ReflectionClass(FinalClass::class)
            )->getMessage()
        );
    }

    public function testAbstractProtectedMethodsNotSupported() : void
    {
        self::assertSame(
            'Provided class "ProxyManagerTestAsset\ClassWithAbstractProtectedMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n"
            . 'ProxyManagerTestAsset\ClassWithAbstractProtectedMethod::protectedAbstractMethod',
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithAbstractProtectedMethod::class)
            )->getMessage()
        );
    }

    public function testProtectedMethodsNotSupported() : void
    {
        self::assertSame(
            'Provided class "ProxyManagerTestAsset\ClassWithProtectedMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n",
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithProtectedMethod::class)
            )->getMessage()
        );
    }

    public function testAbstractPublicMethodsNotSupported() : void
    {
        self::assertSame(
            'Provided class "ProxyManagerTestAsset\ClassWithAbstractPublicMethod" has following protected abstract'
            . ' methods, and therefore cannot be proxied:' . "\n",
            InvalidProxiedClassException::abstractProtectedMethodsNotSupported(
                new ReflectionClass(ClassWithAbstractPublicMethod::class)
            )->getMessage()
        );
    }
}
