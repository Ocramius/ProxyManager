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

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingMethodInterceptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor
     */
    public function testBodyStructure()
    {
        $initializer      = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $valueHolder      = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('bar'));

        $reflection = new MethodReflection('ProxyManagerTestAsset\\BaseClass', 'publicByReferenceParameterMethod');
        $method     = LazyLoadingMethodInterceptor::generateMethod($reflection, $initializer, $valueHolder);

        $this->assertSame('publicByReferenceParameterMethod', $method->getName());
        $this->assertCount(2, $method->getParameters());
        $this->assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, 'publicByReferenceParameterMethod', "
            . "array('param' => \$param, 'byRefParam' => \$byRefParam), \$this->foo);\n\n"
            . "return \$this->bar->publicByReferenceParameterMethod(\$param, \$byRefParam);",
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor
     */
    public function testBodyStructureWithoutParameters()
    {
        $reflectionMethod = new MethodReflection(__CLASS__, 'testBodyStructureWithoutParameters');
        $initializer      = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $valueHolder      = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('bar'));

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $method = LazyLoadingMethodInterceptor::generateMethod($reflectionMethod, $initializer, $valueHolder);

        $this->assertSame('testBodyStructureWithoutParameters', $method->getName());
        $this->assertCount(0, $method->getParameters());
        $this->assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, "
            . "'testBodyStructureWithoutParameters', array(), \$this->foo);\n\n"
            . "return \$this->bar->testBodyStructureWithoutParameters();",
            $method->getBody()
        );
    }
}
