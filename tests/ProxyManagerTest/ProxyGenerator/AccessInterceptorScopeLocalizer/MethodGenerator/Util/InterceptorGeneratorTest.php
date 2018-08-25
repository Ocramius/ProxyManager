<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @group Coverage
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator
 */
class InterceptorGeneratorTest extends TestCase
{
    public function testInterceptorGenerator() : void
    {
        /** @var MethodGenerator|\PHPUnit_Framework_MockObject_MockObject $method */
        $method = $this->createMock(MethodGenerator::class);
        /** @var ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject $bar */
        $bar = $this->createMock(ParameterGenerator::class);
        /** @var ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject $baz */
        $baz = $this->createMock(ParameterGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $baz->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $method->expects(self::any())->method('getName')->will(self::returnValue('fooMethod'));
        $method->expects(self::any())->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        return $prefixReturnValue;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

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
                $prefixInterceptors,
                $suffixInterceptors,
                null
            )
        );
    }

    public function testInterceptorGeneratorWithVoidReturnType() : void
    {
        /** @var MethodGenerator|\PHPUnit_Framework_MockObject_MockObject $method */
        $method = $this->createMock(MethodGenerator::class);
        /** @var ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject $bar */
        $bar = $this->createMock(ParameterGenerator::class);
        /** @var ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject $baz */
        $baz = $this->createMock(ParameterGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $baz->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $method->expects(self::any())->method('getName')->will(self::returnValue('fooMethod'));
        $method->expects(self::any())->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        $prefixReturnValue;
return;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

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
                $prefixInterceptors,
                $suffixInterceptors,
                new \ReflectionMethod(VoidMethodTypeHintedInterface::class, 'returnVoid')
            )
        );
    }

    public function testInterceptorGeneratorWithExistingNonVoidMethod() : void
    {
        /** @var MethodGenerator|\PHPUnit_Framework_MockObject_MockObject $method */
        $method = $this->createMock(MethodGenerator::class);
        /** @var ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject $bar */
        $bar = $this->createMock(ParameterGenerator::class);
        /** @var ParameterGenerator|\PHPUnit_Framework_MockObject_MockObject $baz */
        $baz = $this->createMock(ParameterGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $bar->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $baz->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $method->expects(self::any())->method('getName')->will(self::returnValue('fooMethod'));
        $method->expects(self::any())->method('getParameters')->will(self::returnValue([$bar, $baz]));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        // @codingStandardsIgnoreStart
        $expected = <<<'PHP'
if (isset($this->pre['fooMethod'])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->pre['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnEarly);

    if ($returnEarly) {
        return $prefixReturnValue;
    }
}

$returnValue = "foo";

if (isset($this->post['fooMethod'])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->post['fooMethod']->__invoke($this, $this, 'fooMethod', array('bar' => $bar, 'baz' => $baz), $returnValue, $returnEarly);

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
                $prefixInterceptors,
                $suffixInterceptors,
                new \ReflectionMethod(BaseClass::class, 'publicMethod')
            )
        );
    }
}
