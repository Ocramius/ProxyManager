<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicCloneTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection  = new ReflectionClass(EmptyClass::class);
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer = $this->createMock(PropertyGenerator::class);
        /* @var $initCall MethodGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initCall    = $this->createMock(MethodGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $initCall->expects(self::any())->method('getName')->will(self::returnValue('bar'));

        $magicClone = new MagicClone($reflection, $initializer, $initCall);

        self::assertSame('__clone', $magicClone->getName());
        self::assertCount(0, $magicClone->getParameters());
        self::assertSame(
            "\$this->foo && \$this->bar('__clone', []);",
            $magicClone->getBody()
        );
    }
}
