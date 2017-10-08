<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptor\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
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
        /* @var $suffix PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffix = $this->createMock(PropertyGenerator::class);

        $suffix->expects(self::once())->method('getName')->will(self::returnValue('foo'));

        $setter = new SetMethodSuffixInterceptor($suffix);

        self::assertSame('setMethodSuffixInterceptor', $setter->getName());
        self::assertCount(2, $setter->getParameters());
        self::assertSame('$this->foo[$methodName] = $suffixInterceptor;', $setter->getBody());
    }
}
