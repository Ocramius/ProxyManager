<?php

declare(strict_types=1);

namespace ProxyManagerTest\Generator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;

/**
 * Tests for {@see \ProxyManager\Generator\Util\UniqueIdentifierGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 * @covers \ProxyManager\Generator\Util\UniqueIdentifierGenerator
 */
class UniqueIdentifierGeneratorTest extends TestCase
{
    /**
     * @dataProvider getBaseIdentifierNames
     *
     * @param string $name
     */
    public function testGeneratesUniqueIdentifiers(string $name) : void
    {
        self::assertNotSame(
            UniqueIdentifierGenerator::getIdentifier($name),
            UniqueIdentifierGenerator::getIdentifier($name)
        );
    }

    /**
     * @dataProvider getBaseIdentifierNames
     *
     * @param string $name
     */
    public function testGeneratesValidIdentifiers(string $name) : void
    {
        self::assertRegExp(
            '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/',
            UniqueIdentifierGenerator::getIdentifier($name)
        );
    }

    /**
     * @dataProvider getBaseIdentifierNames
     *
     * @param string $name
     */
    public function testGeneratedIdentifierEntropy(string $name) : void
    {
        self::assertGreaterThan(14, strlen(UniqueIdentifierGenerator::getIdentifier($name)));
    }

    /**
     * Data provider generating identifier names to be checked
     *
     * @return string[][]
     */
    public function getBaseIdentifierNames() : array
    {
        return [
            [''],
            ['1'],
            ['foo'],
            ['Foo'],
            ['bar'],
            ['Bar'],
            ['foo_bar'],
        ];
    }
}
