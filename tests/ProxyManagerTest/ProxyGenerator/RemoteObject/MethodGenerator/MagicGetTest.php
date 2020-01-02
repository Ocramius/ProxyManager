<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet}
 *
 * @group Coverage
 */
final class MagicGetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        $adapter    = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('foo');

        $magicGet = new MagicGet($reflection, $adapter);

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            '$return = $this->foo->call(\'ProxyManagerTestAsset\\\EmptyClass\', \'__get\', array($name));'
            . "\n\nreturn \$return;",
            $magicGet->getBody()
        );
    }
}
