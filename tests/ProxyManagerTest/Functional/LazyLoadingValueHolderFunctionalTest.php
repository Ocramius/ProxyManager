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
use ProxyManager\GeneratorStrategy\BaseGeneratorStrategy;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerTestAsset\BaseClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class LazyLoadingValueHolderFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls($instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy(get_class($instance));

        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|BaseClass */
        $proxy = new $proxyName($this->createInitializer($instance));

        $this->assertFalse($proxy->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterUnSerialization($instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy(get_class($instance));

        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|BaseClass */
        $proxy = unserialize(serialize(new $proxyName($this->createInitializer($instance))));

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
        $this->assertEquals($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterCloning($instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy(get_class($instance));

        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|BaseClass */
        $proxy  = new $proxyName($this->createInitializer($instance));
        $cloned = clone $proxy;

        $this->assertTrue($cloned->isProxyInitialized());
        $this->assertNotSame($proxy->getWrappedValueHolderValue(), $cloned->getWrappedValueHolderValue());
        $this->assertSame($expectedValue, call_user_func_array(array($cloned, $method), $params));
        $this->assertEquals($instance, $cloned->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyReadAccess($instance, $proxy, $publicProperty, $propertyValue)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $this->assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertEquals($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($newValue, $proxy->$publicProperty);
        $this->assertSame($newValue, $proxy->getWrappedValueHolderValue()->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyExistence($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $this->assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertEquals($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyAbsence($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $instance = $proxy->getWrappedValueHolderValue() ? $proxy->getWrappedValueHolderValue() : $instance;
        $instance->$publicProperty = null;
        $this->assertFalse(isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface */

        $instance = $proxy->getWrappedValueHolderValue() ? $proxy->getWrappedValueHolderValue() : $instance;
        unset($proxy->$publicProperty);

        $this->assertTrue($proxy->isProxyInitialized());

        $this->assertFalse(isset($instance->$publicProperty));
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
        $generator          = new LazyLoadingValueHolderGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }

    /**
     * @param object $realInstance
     * @param Mock   $initializerMatcher
     *
     * @return \Closure
     */
    private function createInitializer($realInstance, Mock $initializerMatcher = null)
    {
        $initializerMatcher = $initializerMatcher ?: $this->getMock('stdClass', array('__invoke'));

        return function (
            & $wrappedObject,
            LazyLoadingInterface $proxy,
            $method,
            $params
        ) use (
            $initializerMatcher,
            $realInstance
            ) {
            $proxy->setProxyInitializer(null);

            $wrappedObject = $realInstance;

            $initializerMatcher->__invoke($proxy, $wrappedObject, $method, $params);
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
            array(new BaseClass(), 'publicMethod', array(), 'publicMethodDefault'),
            array(new BaseClass(), 'publicTypeHintedMethod', array(new \stdClass()), 'publicTypeHintedMethodDefault'),
            array(new BaseClass(), 'publicByReferenceMethod', array(), 'publicByReferenceMethodDefault'),
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
                new $proxyName1($this->createInitializer($instance1)),
                'publicProperty',
                'publicPropertyDefault',
            ),
            array(
                $instance2,
                unserialize(serialize(new $proxyName2($this->createInitializer($instance2)))),
                'publicProperty',
                'publicPropertyDefault',
            ),
        );
    }
}
