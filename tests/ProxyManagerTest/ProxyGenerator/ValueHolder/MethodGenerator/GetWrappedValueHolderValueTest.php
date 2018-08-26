<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\ValueHolder\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\TypeGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue}
 *
 * @group Coverage
 */
class GetWrappedValueHolderValueTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $getter = new GetWrappedValueHolderValue($valueHolder);

        self::assertSame('getWrappedValueHolderValue', $getter->getName());
        self::assertCount(0, $getter->getParameters());
        self::assertSame('return $this->foo;', $getter->getBody());
        self::assertEquals(TypeGenerator::fromTypeString('?object'), $getter->getReturnType());
    }
}
