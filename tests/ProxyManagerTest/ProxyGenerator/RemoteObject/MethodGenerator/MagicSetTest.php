<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet}
 *
 * @group Coverage
 */
final class MagicSetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        $adapter    = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('foo');

        $magicGet = new MagicSet($reflection, $adapter);

        self::assertSame('__set', $magicGet->getName());
        self::assertCount(2, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            '$return = $this->foo->call(\'ProxyManagerTestAsset\\\EmptyClass\', \'__set\', array($name, $value));'
            . "\n\nreturn \$return;",
            $magicGet->getBody()
        );
    }
}
