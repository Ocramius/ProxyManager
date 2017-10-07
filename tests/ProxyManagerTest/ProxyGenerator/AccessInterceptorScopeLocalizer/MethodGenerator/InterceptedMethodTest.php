<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod
 * @group Coverage
 */
class InterceptedMethodTest extends TestCase
{
    /**
     * @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $prefixInterceptors;

    /**
     * @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $suffixInterceptors;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $this->prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $this->suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));
    }

    public function testBodyStructure() : void
    {
        $method = InterceptedMethod::generateMethod(
            new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertInstanceOf(MethodGenerator::class, $method);

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertStringMatchesFormat(
            '%a$returnValue = parent::publicByReferenceParameterMethod($param, $byRefParam);%A',
            $method->getBody()
        );
    }

    public function testForwardsVariadicParameters() : void
    {
        $method = InterceptedMethod::generateMethod(
            new MethodReflection(ClassWithMethodWithVariadicFunction::class, 'foo'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertInstanceOf(MethodGenerator::class, $method);

        self::assertSame('foo', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertStringMatchesFormat(
            '%a$returnValue = parent::foo($bar, ...$baz);%A',
            $method->getBody()
        );
    }
}
