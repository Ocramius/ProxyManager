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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructure()
    {
        $valueHolder        = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prefixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $suffixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $reflection         = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\LazyLoading\\MethodGenerator\\ClassWithTwoPublicProperties'
        );

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));

        $constructor = new Constructor($reflection, $valueHolder, $prefixInterceptors, $suffixInterceptors);

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(3, $constructor->getParameters());
        $this->assertSame(
            "unset(\$this->bar, \$this->baz);\n\n\$this->foo = \$wrappedObject;\n\$this->pre = \$prefixInterceptors;"
            . "\n\$this->post = \$suffixInterceptors;",
            $constructor->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructureWithoutPublicProperties()
    {
        $valueHolder        = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prefixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $suffixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $reflection         = new ReflectionClass('ProxyManagerTestAsset\\EmptyClass');

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));

        $constructor = new Constructor($reflection, $valueHolder, $prefixInterceptors, $suffixInterceptors);

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(3, $constructor->getParameters());
        $this->assertSame(
            "\$this->foo = \$wrappedObject;\n\$this->pre = \$prefixInterceptors;"
            . "\n\$this->post = \$suffixInterceptors;",
            $constructor->getBody()
        );
    }
}
