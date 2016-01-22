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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use PHPUnit_Framework_TestCase;
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
class InterceptedMethodTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $prefixInterceptors;

    /**
     * @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $suffixInterceptors;

    protected function setUp()
    {
        parent::setUp();

        $this->prefixInterceptors = $this->getMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->getMock(PropertyGenerator::class);

        $this->prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $this->suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));
    }

    public function testBodyStructure()
    {
        $method = InterceptedMethod::generateMethod(
            new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $this->assertInstanceOf(MethodGenerator::class, $method);

        $this->assertSame('publicByReferenceParameterMethod', $method->getName());
        $this->assertCount(2, $method->getParameters());
        $this->assertStringMatchesFormat(
            '%a$returnValue = parent::publicByReferenceParameterMethod($param, $byRefParam);%A',
            $method->getBody()
        );
    }

    public function testForwardsVariadicParameters()
    {
        $method = InterceptedMethod::generateMethod(
            new MethodReflection(ClassWithMethodWithVariadicFunction::class, 'foo'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $this->assertInstanceOf(MethodGenerator::class, $method);

        $this->assertSame('foo', $method->getName());
        $this->assertCount(2, $method->getParameters());
        $this->assertStringMatchesFormat(
            '%a$returnValue = parent::foo($bar, ...$baz);%A',
            $method->getBody()
        );
    }
}
