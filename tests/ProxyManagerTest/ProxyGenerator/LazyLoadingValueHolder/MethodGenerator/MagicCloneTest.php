<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone}
 *
 * @group Coverage
 */
final class MagicCloneTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection  = new ReflectionClass(EmptyClass::class);
        $initializer = $this->createMock(PropertyGenerator::class);
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');

        $magicClone = new MagicClone($reflection, $initializer, $valueHolder);

        self::assertSame('__clone', $magicClone->getName());
        self::assertCount(0, $magicClone->getParameters());
        self::assertSame(
            '$this->foo && $this->foo->__invoke($this->bar, $this, '
            . "'__clone', array(), \$this->foo);\n\n\$this->bar = clone \$this->bar;",
            $magicClone->getBody()
        );
    }
}
