<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\DisabledMethodException;

/**
 * Tests for {@see \ProxyManager\Exception\DisabledMethodException}
 *
 * @covers \ProxyManager\Exception\DisabledMethodException
 * @group Coverage
 */
final class DisabledMethodExceptionTest extends TestCase
{
    /**
     * @covers \ProxyManager\Exception\DisabledMethodException::disabledMethod
     */
    public function testProxyDirectoryNotFound() : void
    {
        $exception = DisabledMethodException::disabledMethod('foo::bar');

        self::assertSame('Method "foo::bar" is forcefully disabled', $exception->getMessage());
    }
}
