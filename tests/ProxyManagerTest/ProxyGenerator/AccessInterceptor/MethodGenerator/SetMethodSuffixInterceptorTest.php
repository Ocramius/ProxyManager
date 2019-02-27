<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptor\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\TypeGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor}
 *
 * @group Coverage
 */
class SetMethodSuffixInterceptorTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator|MockObject $suffix */
        $suffix = $this->createMock(PropertyGenerator::class);

        $suffix->expects(self::once())->method('getName')->will(self::returnValue('foo'));

        $setter = new SetMethodSuffixInterceptor($suffix);

        self::assertEquals(TypeGenerator::fromTypeString('void'), $setter->getReturnType());
        self::assertSame('setMethodSuffixInterceptor', $setter->getName());
        self::assertCount(2, $setter->getParameters());
        self::assertSame('$this->foo[$methodName] = $suffixInterceptor;', $setter->getBody());
    }
}
