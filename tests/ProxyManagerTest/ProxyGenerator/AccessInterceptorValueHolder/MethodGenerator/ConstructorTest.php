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
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor
     */
    public function testBodyStructure()
    {
        /* @var $valueHolder \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $constructor = Constructor::generateMethod(
            new ReflectionClass(
                'ProxyManagerTestAsset\\ProxyGenerator\\LazyLoading\\MethodGenerator\\ClassWithTwoPublicProperties'
            ),
            $valueHolder
        );

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        $this->assertSame(
            'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?: new \ReflectionClass(\'ProxyManagerTestAsset\\\\ProxyGenerator\\\\LazyLoading\\\\'
            . 'MethodGenerator\\\\ClassWithTwoPublicProperties\');
    $this->foo = $reflection->newInstanceWithoutConstructor();

    unset($this->bar);
    unset($this->baz);
}

$this->foo->__construct();',
            $constructor->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor
     */
    public function testBodyStructureWithoutPublicProperties()
    {
        /* @var $valueHolder \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $constructor = Constructor::generateMethod(
            new ReflectionClass('ProxyManagerTestAsset\\EmptyClass'),
            $valueHolder
        );

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        $this->assertSame(
            'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?: new \ReflectionClass(\'ProxyManagerTestAsset\\\\EmptyClass\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
}

$this->foo->__construct();',
            $constructor->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor
     */
    public function testBodyStructureWithPhp4StyleConstructor()
    {
        $className = uniqid('ClassWithPhp4Constructor');

        eval('class ' . $className . '{ public function ' . $className . '($first, $second, $third) {}}');

        /* @var $valueHolder \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $constructor = Constructor::generateMethod(
            new ReflectionClass($className),
            $valueHolder
        );

        $this->assertSame($className, $constructor->getName());
        $this->assertCount(3, $constructor->getParameters());
        $this->assertSame(
            'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?: new \ReflectionClass(\'' . $className . '\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
}

$this->foo->' . $className . '($first, $second, $third);',
            $constructor->getBody()
        );
    }
}
