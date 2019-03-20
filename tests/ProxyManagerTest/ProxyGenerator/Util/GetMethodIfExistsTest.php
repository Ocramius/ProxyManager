<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\GetMethodIfExists;
use ReflectionClass;
use ReflectionMethod;
use function uniqid;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\GetMethodIfExists}
 *
 * @covers \ProxyManager\ProxyGenerator\Util\GetMethodIfExists
 * @group Coverage
 */
final class GetMethodIfExistsTest extends TestCase
{
    public function testGetExistingMethod() : void
    {
        $method = GetMethodIfExists::get(new ReflectionClass(self::class), 'testGetExistingMethod');

        self::assertInstanceOf(ReflectionMethod::class, $method);
        self::assertSame('testGetExistingMethod', $method->getName());
        self::assertSame(self::class, $method->getDeclaringClass()->getName());
    }

    public function testGetNonExistingMethod() : void
    {
        self::assertNull(GetMethodIfExists::get(new ReflectionClass(self::class), uniqid('nonExisting', true)));
    }
}
