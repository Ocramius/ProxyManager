<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\NullObject\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor;
use ProxyManagerTestAsset\BaseClass;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class NullObjectMethodInterceptorTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructure() : void
    {
        $reflection = new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod');
        $method     = NullObjectMethodInterceptor::generateMethod($reflection);

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertNull($method->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructureWithoutParameters() : void
    {
        $reflectionMethod = new MethodReflection(__CLASS__, 'testBodyStructureWithoutParameters');

        $method = NullObjectMethodInterceptor::generateMethod($reflectionMethod);

        self::assertSame('testBodyStructureWithoutParameters', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertNull($method->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructureWithoutByRefReturn() : void
    {
        $reflectionMethod = new MethodReflection('ProxyManagerTestAsset\BaseClass', 'publicByReferenceMethod');

        $method = NullObjectMethodInterceptor::generateMethod($reflectionMethod);

        self::assertSame('publicByReferenceMethod', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertStringMatchesFormat("\$ref%s = null;\nreturn \$ref%s;", $method->getBody());
    }
}
