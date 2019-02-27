<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy}
 *
 * @group Coverage
 */
class InitializeProxyTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator|MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');

        $initializeProxy = new InitializeProxy($initializer, $valueHolder);

        self::assertSame('initializeProxy', $initializeProxy->getName());
        self::assertCount(0, $initializeProxy->getParameters());
        self::assertSame(
            'return $this->foo && $this->foo->__invoke($this->bar, $this, \'initializeProxy\', array(), $this->foo);',
            $initializeProxy->getBody()
        );
        self::assertStringMatchesFormat('%A : bool%A', $initializeProxy->generate(), 'Return type hint is boolean');
    }
}
