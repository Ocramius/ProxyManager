<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\MethodGenerator;
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
 */
class InterceptorGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator
     */
    public function testInterceptorGenerator()
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
                $suffixInterceptors
            )
        );
    }
}
