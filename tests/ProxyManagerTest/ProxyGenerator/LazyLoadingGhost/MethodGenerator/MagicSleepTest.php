<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep}
 *
 * @group Coverage
 */
final class MagicSleepTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        /** @var PropertyGenerator&MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var MethodGenerator&MockObject $initMethod */
        $initMethod = $this->createMock(MethodGenerator::class);

        $initializer->method('getName')->willReturn('foo');
        $initMethod->method('getName')->willReturn('bar');

        $magicSleep = new MagicSleep($reflection, $initializer, $initMethod);

        self::assertSame('__sleep', $magicSleep->getName());
        self::assertCount(0, $magicSleep->getParameters());
        self::assertSame(
            "\$this->foo && \$this->bar('__sleep', []);"
            . "\n\nreturn array_keys((array) \$this);",
            $magicSleep->getBody()
        );
    }
}
