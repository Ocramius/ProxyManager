<?php

declare(strict_types=1);

namespace ProxyManagerTest;

use PHPUnit_Framework_TestCase;
use ProxyManager\Version;

/**
 * Tests for {@see \ProxyManager\Version}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Version
 * @group Coverage
 */
class VersionTest extends PHPUnit_Framework_TestCase
{
    public function testGetVersion() : void
    {
        $version = Version::getVersion();

        self::assertInternalType('string', $version);
        self::assertNotEmpty($version);
        self::assertStringMatchesFormat('%A@%A', $version);
    }
}
