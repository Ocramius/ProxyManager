<?php

declare(strict_types=1);

namespace ProxyManagerTest\FileLocator;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\InvalidProxyDirectoryException;
use ProxyManager\FileLocator\FileLocator;
use const DIRECTORY_SEPARATOR;

/**
 * Tests for {@see \ProxyManager\FileLocator\FileLocator}
 *
 * @group Coverage
 */
final class FileLocatorTest extends TestCase
{
    /**
     * @covers \ProxyManager\FileLocator\FileLocator::__construct
     * @covers \ProxyManager\FileLocator\FileLocator::getProxyFileName
     */
    public function testGetProxyFileName() : void
    {
        $locator = new FileLocator(__DIR__);

        self::assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'FooBarBaz.php', $locator->getProxyFileName('Foo\\Bar\\Baz'));
        self::assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'Foo_Bar_Baz.php', $locator->getProxyFileName('Foo_Bar_Baz'));
    }

    /**
     * @covers \ProxyManager\FileLocator\FileLocator::__construct
     */
    public function testRejectsNonExistingDirectory() : void
    {
        $this->expectException(InvalidProxyDirectoryException::class);
        new FileLocator(__DIR__ . '/non-existing');
    }
}
