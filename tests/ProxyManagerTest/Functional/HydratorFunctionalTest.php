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

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_TestCase;
use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\HydratorProxyGenerator;
use ProxyManagerTestAsset\HydratedObject;
use ReflectionClass;
use ProxyManager\Generator\ClassGenerator;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\HydratedObject} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class HydratorFunctionalTest extends PHPUnit_Framework_TestCase
{
    public function testHydrate()
    {
        $object      = new HydratedObject();
        $proxy       = $this->generateProxy('ProxyManagerTestAsset\HydratedObject');
        $initialData = array('foo' => 1, 'bar' => 2, 'baz' => 3);
        $data        = array('foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz');

        $this->assertSame($initialData['foo'], $object->__get('foo'));
        $this->assertSame($initialData['bar'], $object->__get('bar'));
        $this->assertSame($initialData['baz'], $object->__get('baz'));
        $this->assertSame($initialData, $proxy->extract($object));

        $proxy->hydrate($data, $object);

        $this->assertSame($data['foo'], $object->__get('foo'));
        $this->assertSame($data['bar'], $object->__get('bar'));
        $this->assertSame($data['baz'], $object->__get('baz'));
        $this->assertSame($data, $proxy->extract($object));
    }

    public function testDisabledMethod()
    {
        $proxy = $this->generateProxy('ProxyManagerTestAsset\HydratedObject');

        $this->setExpectedException('ProxyManager\Exception\DisabledMethodException');
        $proxy->doFoo();
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param  string $parentClassName
     *
     * @return \ProxyManagerTestAsset\HydratedObject|\ProxyManager\Proxy\HydratorInterface
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\Foo' . uniqid();
        $generator          = new HydratorProxyGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();
        $reflection         = new ReflectionClass($parentClassName);

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        $privateMethods = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
        $accessors      = array();

        foreach ($privateMethods as $privateMethod) {
            $privateMethod->setAccessible(true);

            $accessors[$privateMethod->getName()] = $privateMethod;
        }

        return new $generatedClassName($accessors);
    }
}
