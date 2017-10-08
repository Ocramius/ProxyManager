<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\MethodGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use Zend\Code\Generator\ParameterGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator
 */
class InterceptorGeneratorTest extends TestCase
{
    public function testInterceptorGenerator() : void
    {
        /* @var $method MethodGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $method             = $this->createMock(MethodGenerator::class);
        /* @var $bar ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $bar                = $this->createMock(ParameterGenerator::class);
        /* @var $baz ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $baz                = $this->createMock(ParameterGenerator::class);
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $baz->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $method->expects(self::any())->method('getName')->will(self::returnValue('fooMethod'));
        $method->expects(self::any())->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

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
        /* @var $method MethodGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $method             = $this->createMock(MethodGenerator::class);
        /* @var $bar ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $bar                = $this->createMock(ParameterGenerator::class);
        /* @var $baz ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $baz                = $this->createMock(ParameterGenerator::class);
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $baz->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $method->expects(self::any())->method('getName')->will(self::returnValue('fooMethod'));
        $method->expects(self::any())->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

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
                new \ReflectionMethod(VoidMethodTypeHintedInterface::class, 'returnVoid')
            )
        );
    }

    public function testInterceptorGeneratorWithNonVoidOriginalMethod() : void
    {
        /* @var $method MethodGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $method             = $this->createMock(MethodGenerator::class);
        /* @var $bar ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $bar                = $this->createMock(ParameterGenerator::class);
        /* @var $baz ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $baz                = $this->createMock(ParameterGenerator::class);
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $baz->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $method->expects(self::any())->method('getName')->will(self::returnValue('fooMethod'));
        $method->expects(self::any())->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

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
                new \ReflectionMethod(BaseClass::class, 'publicMethod')
            )
        );
    }
}
