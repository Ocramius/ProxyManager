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

use Closure;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use ProxyManager\Configuration;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManagerTestAsset\BaseClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class LazyLoadingGhostFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|BaseClass */
        $proxy = new $proxyName($this->createInitializer($className, $instance));

        $this->assertFalse($proxy->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterUnSerialization($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|BaseClass */
        $proxy = unserialize(serialize(new $proxyName($this->createInitializer($className, $instance))));

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterCloning($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|BaseClass */
        $proxy  = new $proxyName($this->createInitializer($className, $instance));
        $cloned = clone $proxy;

        $this->assertTrue($cloned->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array(array($cloned, $method), $params));
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyReadAccess($instance, $proxy, $publicProperty, $propertyValue)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface */
        $this->assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface */
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($newValue, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyExistence($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface */
        $this->assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyAbsence($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface */
        $proxy->$publicProperty = null;
        $this->assertFalse(isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface */

        unset($proxy->$publicProperty);

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertTrue(isset($instance->$publicProperty));
        $this->assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param  string $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\Foo' . uniqid();
        $generator          = new LazyLoadingGhostGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }

    /**
     * @param string $className
     * @param object $realInstance
     * @param Mock   $initializerMatcher
     *
     * @return \Closure
     */
    private function createInitializer($className, $realInstance, Mock $initializerMatcher = null)
    {
        if (null === $initializerMatcher) {
            $initializerMatcher = $this->getMock('stdClass', array('__invoke'));

            $initializerMatcher
                ->expects($this->once())
                ->method('__invoke')
                ->with(
                    $this->logicalAnd(
                        $this->isInstanceOf('ProxyManager\\Proxy\\LazyLoadingInterface'),
                        $this->isInstanceOf($className)
                    )
                );
        }

        $initializerMatcher = $initializerMatcher ?: $this->getMock('stdClass', array('__invoke'));

        return function (
            LazyLoadingInterface $proxy,
            $method,
            $params,
            & $initializer
        ) use (
            $initializerMatcher,
            $realInstance
            ) {
            $initializer     = null;
            $reflectionClass = new ReflectionClass($realInstance);

            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true);
                $property->setValue($proxy, $property->getValue($realInstance));
            }

            $initializerMatcher->__invoke($proxy, $method, $params);
        };
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return array
     */
    public function getProxyMethods()
    {
        return array(
            array(
                'ProxyManagerTestAsset\\BaseClass',
                new BaseClass(),
                'publicMethod',
                array(),
                'publicMethodDefault'
            ),
            array(
                'ProxyManagerTestAsset\\BaseClass',
                new BaseClass(),
                'publicTypeHintedMethod',
                array(new \stdClass()),
                'publicTypeHintedMethodDefault'
            ),
            array(
                'ProxyManagerTestAsset\\BaseClass',
                new BaseClass(),
                'publicByReferenceMethod',
                array(),
                'publicByReferenceMethodDefault'
            ),
        );
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies()
    {
        $instance1 = new BaseClass();
        $proxyName1 = $this->generateProxy(get_class($instance1));
        $instance2 = new BaseClass();
        $proxyName2 = $this->generateProxy(get_class($instance2));

        return array(
            array(
                $instance1,
                new $proxyName1($this->createInitializer('ProxyManagerTestAsset\\BaseClass', $instance1)),
                'publicProperty',
                'publicPropertyDefault',
            ),
            array(
                $instance2,
                unserialize(
                    serialize(new $proxyName2($this->createInitializer('ProxyManagerTestAsset\\BaseClass', $instance2)))
                ),
                'publicProperty',
                'publicPropertyDefault',
            ),
        );
    }
}
