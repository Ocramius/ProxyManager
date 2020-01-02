<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized}
 *
 * @group Coverage
 */
final class IsProxyInitializedTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized::__construct
     */
    public function testBodyStructure() : void
    {
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->method('getName')->willReturn('bar');

        $isProxyInitialized = new IsProxyInitialized($valueHolder);

        self::assertSame('isProxyInitialized', $isProxyInitialized->getName());
        self::assertCount(0, $isProxyInitialized->getParameters());
        self::assertSame('return null !== $this->bar;', $isProxyInitialized->getBody());
        self::assertStringMatchesFormat('%A : bool%A', $isProxyInitialized->generate(), 'Return type hint is boolean');
    }
}
