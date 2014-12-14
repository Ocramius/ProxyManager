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
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
class StaticProxyConstructorTest extends PHPUnit_Framework_TestCase
{
    public function testBodyStructure()
    {
        /* @var $valueHolder \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        /* @var $prefixInterceptors \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        /* @var $suffixInterceptors \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(
                'ProxyManagerTestAsset\\ProxyGenerator\\LazyLoading\\MethodGenerator\\ClassWithTwoPublicProperties'
            ),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        $this->assertSame('staticProxyConstructor', $constructor->getName());
        $this->assertCount(3, $constructor->getParameters());
        $this->assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

unset($instance->bar, $instance->baz);

$instance->foo = $wrappedObject;
$instance->pre = $prefixInterceptors;
$instance->post = $suffixInterceptors;

return $instance;',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithoutPublicProperties()
    {
        /* @var $valueHolder \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        /* @var $prefixInterceptors \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        /* @var $suffixInterceptors \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));

        $constructor = new StaticProxyConstructor(
            new ReflectionClass('ProxyManagerTestAsset\\EmptyClass'),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        $this->assertSame('staticProxyConstructor', $constructor->getName());
        $this->assertCount(3, $constructor->getParameters());
        $this->assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

$instance->foo = $wrappedObject;
$instance->pre = $prefixInterceptors;
$instance->post = $suffixInterceptors;

return $instance;',
            $constructor->getBody()
        );
    }
}
