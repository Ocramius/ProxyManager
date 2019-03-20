<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use ReflectionMethod;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @group Coverage
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator
 */
final class InterceptorGeneratorTest extends TestCase
{
    public function testInterceptorGenerator() : void
    {
        /** @var MethodGenerator&MockObject $method */
        $method = $this->createMock(MethodGenerator::class);
        /** @var ParameterGenerator&MockObject $bar */
        $bar = $this->createMock(ParameterGenerator::class);
        /** @var ParameterGenerator&MockObject $baz */
        $baz = $this->createMock(ParameterGenerator::class);
        /** @var PropertyGenerator&MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->method('getName')->willReturn('bar');
        $baz->method('getName')->willReturn('baz');
        $method->method('getName')->willReturn('fooMethod');
        $method->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $valueHolder->method('getName')->willReturn('foo');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this->foo, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        return $prefixReturnValue;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this->foo, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

    if ($returnEarly) {
        return $suffixReturnValue;
    }
}

return $returnValue;
PHP;
        // @codingStandardsIgnoreEnd

        self::assertSame(
            $expected,
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = "foo";',
                $method,
                $valueHolder,
                $prefixInterceptors,
                $suffixInterceptors,
                null
            )
        );
    }

    public function testInterceptorGeneratorWithVoidMethod() : void
    {
        /** @var MethodGenerator&MockObject $method */
        $method = $this->createMock(MethodGenerator::class);
        /** @var ParameterGenerator&MockObject $bar */
        $bar = $this->createMock(ParameterGenerator::class);
        /** @var ParameterGenerator&MockObject $baz */
        $baz = $this->createMock(ParameterGenerator::class);
        /** @var PropertyGenerator&MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->method('getName')->willReturn('bar');
        $baz->method('getName')->willReturn('baz');
        $method->method('getName')->willReturn('fooMethod');
        $method->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $valueHolder->method('getName')->willReturn('foo');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this->foo, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        $prefixReturnValue;
return;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this->foo, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

    if ($returnEarly) {
        $suffixReturnValue;
return;
    }
}

$returnValue;
return;
PHP;
        // @codingStandardsIgnoreEnd

        self::assertSame(
            $expected,
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = "foo";',
                $method,
                $valueHolder,
                $prefixInterceptors,
                $suffixInterceptors,
                new ReflectionMethod(VoidMethodTypeHintedInterface::class, 'returnVoid')
            )
        );
    }

    public function testInterceptorGeneratorWithNonVoidOriginalMethod() : void
    {
        /** @var MethodGenerator&MockObject $method */
        $method = $this->createMock(MethodGenerator::class);
        /** @var ParameterGenerator&MockObject $bar */
        $bar = $this->createMock(ParameterGenerator::class);
        /** @var ParameterGenerator&MockObject $baz */
        $baz = $this->createMock(ParameterGenerator::class);
        /** @var PropertyGenerator&MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->method('getName')->willReturn('bar');
        $baz->method('getName')->willReturn('baz');
        $method->method('getName')->willReturn('fooMethod');
        $method->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $valueHolder->method('getName')->willReturn('foo');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this->foo, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        return $prefixReturnValue;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this->foo, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

    if ($returnEarly) {
        return $suffixReturnValue;
    }
}

return $returnValue;
PHP;
        // @codingStandardsIgnoreEnd

        self::assertSame(
            $expected,
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = "foo";',
                $method,
                $valueHolder,
                $prefixInterceptors,
                $suffixInterceptors,
                new ReflectionMethod(BaseClass::class, 'publicMethod')
            )
        );
    }
}
