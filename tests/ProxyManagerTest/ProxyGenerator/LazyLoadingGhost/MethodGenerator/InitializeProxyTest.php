<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy}
 *
 * @group Coverage
 */
final class InitializeProxyTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy::__construct
     */
    public function testBodyStructure() : void
    {
        $initializer = $this->createMock(PropertyGenerator::class);
        $initCall    = $this->createMock(MethodGenerator::class);

        $initializer->method('getName')->willReturn('foo');
        $initCall->method('getName')->willReturn('bar');

        $initializeProxy = new InitializeProxy($initializer, $initCall);

        self::assertSame('initializeProxy', $initializeProxy->getName());
        self::assertCount(0, $initializeProxy->getParameters());
        self::assertSame(
            'return $this->foo && $this->bar(\'initializeProxy\', []);',
            $initializeProxy->getBody()
        );
        self::assertStringMatchesFormat('%A : bool%A', $initializeProxy->generate(), 'Return type hint is boolean');
    }
}
