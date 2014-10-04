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
use PHPUnit_Framework_MockObject_MockObject as Mock;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class LazyLoadingGhostFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface|BaseClass */
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

        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface|BaseClass */
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

        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface|BaseClass */
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
        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface */
        $this->assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface */
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
        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface */
        $this->assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyAbsence($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface */
        $proxy->$publicProperty = null;
        $this->assertFalse(isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface */

        unset($proxy->$publicProperty);

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertTrue(isset($instance->$publicProperty));
        $this->assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Verifies that accessing a public property containing an array behaves like in a normal context
     */
    public function testCanWriteToArrayKeysInPublicProperty()
    {
        $instance    = new ClassWithPublicArrayProperty();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicArrayProperty */
        $proxy       = new $proxyName($initializer);

        $proxy->arrayProperty['foo'] = 'bar';

        $this->assertSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = array('tab' => 'taz');

        $this->assertSame(array('tab' => 'taz'), $proxy->arrayProperty);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object itself
     */
    public function testWillNotModifyRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = new $proxyName($initializer);
        $variable    = $proxy->property0;

        $this->assertSame('property0', $variable);

        $variable = 'foo';

        $this->assertSame('property0', $proxy->property0);
    }

    /**
     * Verifies that public properties references retrieved via `__get` modify in the object state
     */
    public function testWillModifyByRefRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = new $proxyName($initializer);
        $variable    = & $proxy->property0;

        $this->assertSame('property0', $variable);

        $variable = 'foo';

        $this->assertSame('foo', $proxy->property0);
    }

    public function testKeepsInitializerWhenNotOverwitten()
    {
        $instance    = new BaseClass();
        $proxyName   = $this->generateProxy(get_class($instance));
        $initializer = function () {
        };
        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface */
        $proxy       = new $proxyName($initializer);

        $proxy->initializeProxy();

        $this->assertSame($initializer, $proxy->getProxyInitializer());
    }

    /**
     * Verifies that public properties are not being initialized multiple times
     */
    public function testKeepsInitializedPublicProperties()
    {
        $instance    = new BaseClass();
        $proxyName   = $this->generateProxy(get_class($instance));
        $initializer = function (BaseClass $proxy, $method, $parameters, & $initializer) {
            $initializer           = null;
            $proxy->publicProperty = 'newValue';
        };
        /* @var $proxy \ProxyManager\Proxy\GhostObjectInterface|BaseClass */
        $proxy       = new $proxyName($initializer);

        $proxy->initializeProxy();
        $this->assertSame('newValue', $proxy->publicProperty);

        $proxy->publicProperty = 'otherValue';

        $proxy->initializeProxy();

        $this->assertSame('otherValue', $proxy->publicProperty);
    }

    /**
     * Verifies that properties' default values are preserved
     */
    public function testPublicPropertyDefaultWillBePreserved()
    {
        $instance    = new ClassWithPublicProperties();
        $proxyName   = $this->generateProxy(get_class($instance));
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = new $proxyName(function () {
        });

        $this->assertSame('property0', $proxy->property0);
    }

    /**
     * Verifies that protected properties' default values are preserved
     */
    public function testProtectedPropertyDefaultWillBePreserved()
    {
        $instance    = new ClassWithProtectedProperties();
        $proxyName   = $this->generateProxy(get_class($instance));
        /* @var $proxy ClassWithProtectedProperties */
        $proxy       = new $proxyName(function () {
        });

        // Check protected property via reflection
        $reflectionProperty = new ReflectionProperty($instance, 'property0');
        $reflectionProperty->setAccessible(true);

        $this->assertSame('property0', $reflectionProperty->getValue($proxy));
    }

    /**
     * Verifies that private properties' default values are preserved
     */
    public function testPrivatePropertyDefaultWillBePreserved()
    {
        $instance  = new ClassWithPrivateProperties();
        $proxyName = $this->generateProxy(get_class($instance));
        /* @var $proxy ClassWithPrivateProperties */
        $proxy     = new $proxyName(function () {
        });

        // Check protected property via reflection
        $reflectionProperty = new ReflectionProperty($instance, 'property0');
        $reflectionProperty->setAccessible(true);

        $this->assertSame('property0', $reflectionProperty->getValue($proxy));
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
                        $this->isInstanceOf('ProxyManager\\Proxy\\GhostObjectInterface'),
                        $this->isInstanceOf($className)
                    )
                );
        }

        $initializerMatcher = $initializerMatcher ?: $this->getMock('stdClass', array('__invoke'));

        return function (
            GhostObjectInterface $proxy,
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
        $selfHintParam = new ClassWithSelfHint();

        $data = array(
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

        if (PHP_VERSION_ID >= 50401) {
            // PHP < 5.4.1 misbehaves, throwing strict standards, see https://bugs.php.net/bug.php?id=60573
            $data[] = array(
                'ProxyManagerTestAsset\\ClassWithSelfHint',
                new ClassWithSelfHint(),
                'selfHintMethod',
                array('parameter' => $selfHintParam),
                $selfHintParam
            );
        }

        return $data;
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
