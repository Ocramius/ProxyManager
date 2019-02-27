<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptor\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\TypeGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor}
 *
 * @group Coverage
 */
class SetMethodPrefixInterceptorTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator|MockObject $suffix */
        $suffix = $this->createMock(PropertyGenerator::class);

        $suffix->expects(self::once())->method('getName')->willReturn('foo');

        $setter = new SetMethodPrefixInterceptor($suffix);

        self::assertEquals(TypeGenerator::fromTypeString('void'), $setter->getReturnType());
        self::assertSame('setMethodPrefixInterceptor', $setter->getName());
        self::assertCount(2, $setter->getParameters());
        self::assertSame('$this->foo[$methodName] = $prefixInterceptor;', $setter->getBody());
    }
}
