<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicUnsetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection   = new ReflectionClass(EmptyClass::class);
        /* @var $adapter PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $adapter      = $this->createMock(PropertyGenerator::class);
        $adapter->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $magicGet     = new MagicUnset($reflection, $adapter);

        self::assertSame('__unset', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            '$return = $this->foo->call(\'ProxyManagerTestAsset\\\EmptyClass\', \'__unset\', array($name));'
            . "\n\nreturn \$return;",
            $magicGet->getBody()
        );
    }
}
