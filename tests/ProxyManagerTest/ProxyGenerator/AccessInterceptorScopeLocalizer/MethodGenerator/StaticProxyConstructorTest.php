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
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
class StaticProxyConstructorTest extends PHPUnit_Framework_TestCase
{
    private $prefixInterceptors;
    private $suffixInterceptors;
    public function setUp()
    {
        $this->prefixInterceptors = $this->getMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->getMock(PropertyGenerator::class);

        $this->prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $this->suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));
    }

    public function testSignature()
    {
        $method = new StaticProxyConstructor(
            new ReflectionClass(ClassWithProtectedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );
        $this->assertSame('staticProxyConstructor', $method->getName());

        $parameters = $method->getParameters();

        $this->assertCount(3, $parameters);

        $this->assertSame(
            ClassWithProtectedProperties::class,
            $parameters['localizedObject']->getType()
        );
        $this->assertSame('array', $parameters['prefixInterceptors']->getType());
        $this->assertSame('array', $parameters['suffixInterceptors']->getType());
    }

    public function testBodyStructure()
    {
        $method = new StaticProxyConstructor(
            new ReflectionClass(ClassWithPublicProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $this->assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance   = $reflection->newInstanceWithoutConstructor();

$instance->bindProxyProperties($localizedObject, $prefixInterceptors, $suffixInterceptors);

return $instance;',
            $method->getBody()
        );
    }
}
