<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\InvalidProxyDirectoryException;

/**
 * Tests for {@see \ProxyManager\Exception\InvalidProxyDirectoryException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Exception\InvalidProxyDirectoryException
 * @group Coverage
 */
class InvalidProxyDirectoryExceptionTest extends TestCase
{
    /**
     * @covers \ProxyManager\Exception\InvalidProxyDirectoryException::proxyDirectoryNotFound
     */
    public function testProxyDirectoryNotFound() : void
    {
        $exception = InvalidProxyDirectoryException::proxyDirectoryNotFound('foo/bar');

        self::assertSame('Provided directory "foo/bar" does not exist', $exception->getMessage());
    }
}
