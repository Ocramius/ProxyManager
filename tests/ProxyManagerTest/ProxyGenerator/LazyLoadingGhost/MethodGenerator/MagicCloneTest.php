<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone}
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
        $reflection = new ReflectionClass(EmptyClass::class);
        /** @var PropertyGenerator|MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var MethodGenerator|MockObject $initCall */
        $initCall = $this->createMock(MethodGenerator::class);

        $initializer->method('getName')->willReturn('foo');
        $initCall->method('getName')->willReturn('bar');

        $magicClone = new MagicClone($reflection, $initializer, $initCall);

        self::assertSame('__clone', $magicClone->getName());
        self::assertCount(0, $magicClone->getParameters());
        self::assertSame(
            "\$this->foo && \$this->bar('__clone', []);",
            $magicClone->getBody()
        );
    }
}
