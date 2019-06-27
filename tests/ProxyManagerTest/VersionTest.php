<?php

declare(strict_types=1);

namespace ProxyManagerTest;

use PHPUnit\Framework\TestCase;
use ProxyManager\Version;

/**
 * Tests for {@see \ProxyManager\Version}
 *
 * @covers \ProxyManager\Version
 * @group Coverage
 */
final class VersionTest extends TestCase
{
    public static function testGetVersion() : void
    {
        $version = Version::getVersion();

        self::assertStringMatchesFormat('%A@%A', $version);
    }
}
