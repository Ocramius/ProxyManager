<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue}
 *
 * @group Coverage
 */
final class GetWrappedValueHolderValueTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue::__construct
     */
    public function testBodyStructure() : void
    {
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('foo');

        $getter = new GetWrappedValueHolderValue($valueHolder);

        self::assertSame('getWrappedValueHolderValue', $getter->getName());
        self::assertCount(0, $getter->getParameters());
        self::assertSame('return $this->foo;', $getter->getBody());
        self::assertEquals(TypeGenerator::fromTypeString('?object'), $getter->getReturnType());
    }
}
