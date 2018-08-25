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
class VersionTest extends TestCase
{
    public function testGetVersion() : void
    {
        $version = Version::getVersion();

        self::assertInternalType('string', $version);
        self::assertNotEmpty($version);
        self::assertStringMatchesFormat('%A@%A', $version);
    }
}
