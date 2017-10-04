<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit_Framework_TestCase;
use ProxyManager\Exception\DisabledMethodException;

/**
 * Tests for {@see \ProxyManager\Exception\DisabledMethodException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Exception\DisabledMethodException
 * @group Coverage
 */
class DisabledMethodExceptionTest extends PHPUnit_Framework_TestCase
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
