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

namespace ProxyManagerTest\ProxyGenerator\LazyLoading\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
class StaticProxyConstructorTest extends PHPUnit_Framework_TestCase
{
    public function testBodyStructure()
    {
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer = $this->getMock(PropertyGenerator::class);

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(
                ClassWithTwoPublicProperties::class
            ),
            $initializer
        );

        $this->assertSame('staticProxyConstructor', $constructor->getName());
        $this->assertCount(1, $constructor->getParameters());
        $this->assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

unset($instance->bar, $instance->baz);

$instance->foo = $initializer;

return $instance;',
            $constructor->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructureWithoutPublicProperties()
    {
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer = $this->getMock(PropertyGenerator::class);

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(EmptyClass::class),
            $initializer
        );

        $this->assertSame('staticProxyConstructor', $constructor->getName());
        $this->assertCount(1, $constructor->getParameters());
        $this->assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

$instance->foo = $initializer;

return $instance;',
            $constructor->getBody()
        );
    }
}
