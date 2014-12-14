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
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\NullObjectGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObjectGenerator} produced objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class NullObjectFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        $proxy     = $proxyName::staticProxyConstructor();

        $this->assertSame(null, call_user_func_array([$proxy, $method], $params));
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterUnSerialization($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);
        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        $proxy     = unserialize(serialize($proxyName::staticProxyConstructor()));

        $this->assertSame(null, call_user_func_array([$proxy, $method], $params));
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterCloning($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        $proxy     = $proxyName::staticProxyConstructor();
        $cloned    = clone $proxy;

        $this->assertSame(null, call_user_func_array([$cloned, $method], $params));
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyReadAccess($instance, $proxy, $publicProperty, $propertyValue)
    {
        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        $this->assertSame(null, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        $this->assertSame($newValue, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyExistence($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        $this->assertSame(null, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        unset($proxy->$publicProperty);

        $this->assertTrue(isset($instance->$publicProperty));
        $this->assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new NullObjectGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return array
     */
    public function getProxyMethods()
    {
        $selfHintParam = new ClassWithSelfHint();

        return [
            [
                BaseClass::class,
                new BaseClass(),
                'publicMethod',
                [],
                'publicMethodDefault'
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'publicTypeHintedMethod',
                ['param' => new \stdClass()],
                'publicTypeHintedMethodDefault'
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'publicByReferenceMethod',
                [],
                'publicByReferenceMethodDefault'
            ],
            [
                BaseInterface::class,
                new BaseClass(),
                'publicMethod',
                [],
                'publicMethodDefault'
            ],
            [
                ClassWithSelfHint::class,
                new ClassWithSelfHint(),
                'selfHintMethod',
                ['parameter' => $selfHintParam],
                $selfHintParam
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies()
    {
        $instance1  = new BaseClass();
        $proxyName1 = $this->generateProxy(get_class($instance1));
        $instance2  = new BaseClass();
        $proxyName2 = $this->generateProxy(get_class($instance2));

        return [
            [
                $instance1,
                $proxyName1::staticProxyConstructor($instance1),
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                $instance2,
                unserialize(serialize($proxyName2::staticProxyConstructor($instance2))),
                'publicProperty',
                'publicPropertyDefault',
            ],
        ];
    }
}
