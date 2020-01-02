<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone}
 *
 * @group Coverage
 */
final class MagicCloneTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection  = new ReflectionClass(EmptyClass::class);
        $initializer = $this->createMock(PropertyGenerator::class);
        $initCall    = $this->createMock(MethodGenerator::class);

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
