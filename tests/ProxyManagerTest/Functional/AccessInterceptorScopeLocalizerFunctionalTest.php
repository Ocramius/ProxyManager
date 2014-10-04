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

use PHPUnit_Framework_SkippedTestError;
use PHPUnit_Framework_TestCase;
use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ReflectionClass;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class AccessInterceptorScopeLocalizerFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass()
    {
        if (! method_exists('Closure', 'bind')) {
            throw new PHPUnit_Framework_SkippedTestError(
                'PHP 5.3 doesn\'t support scope localization of private properties'
            );
        }
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        $proxy     = new $proxyName($instance);

        $this->assertProxySynchronized($instance, $proxy);
        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));

        $listener  = $this->getMock('stdClass', array('__invoke'));
        $listener
            ->expects($this->once())
            ->method('__invoke')
            ->with($proxy, $proxy, $method, $params, false);

        $proxy->setMethodPrefixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, & $returnEarly) use ($listener) {
                $listener->__invoke($proxy, $instance, $method, $params, $returnEarly);
            }
        );

        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));

        $random = uniqid();

        $proxy->setMethodPrefixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, & $returnEarly) use ($random) {
                $returnEarly = true;

                return $random;
            }
        );

        $this->assertSame($random, call_user_func_array(array($proxy, $method), $params));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsWithSuffixListener($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        $proxy     = new $proxyName($instance);
        $listener  = $this->getMock('stdClass', array('__invoke'));
        $listener
            ->expects($this->once())
            ->method('__invoke')
            ->with($proxy, $proxy, $method, $params, $expectedValue, false);

        $proxy->setMethodSuffixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, $returnValue, & $returnEarly) use ($listener) {
                $listener->__invoke($proxy, $instance, $method, $params, $returnValue, $returnEarly);
            }
        );

        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));

        $random = uniqid();

        $proxy->setMethodSuffixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, $returnValue, & $returnEarly) use ($random) {
                $returnEarly = true;

                return $random;
            }
        );

        $this->assertSame($random, call_user_func_array(array($proxy, $method), $params));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterUnSerialization($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);
        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        $proxy     = unserialize(serialize(new $proxyName($instance)));

        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterCloning($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        $proxy     = new $proxyName($instance);
        $cloned    = clone $proxy;

        $this->assertProxySynchronized($instance, $proxy);
        $this->assertSame($expectedValue, call_user_func_array(array($cloned, $method), $params));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyReadAccess($instance, $proxy, $publicProperty, $propertyValue)
    {
        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        $this->assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        $this->assertSame($newValue, $proxy->$publicProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyExistence($instance, $proxy, $publicProperty)
    {
        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        $this->assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);

        $instance->$publicProperty = null;
        $this->assertFalse(isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset($instance, $proxy, $publicProperty)
    {
        $this->markTestSkipped('It is currently not possible to synchronize properties un-setting');
        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface */
        unset($proxy->$publicProperty);

        $this->assertFalse(isset($instance->$publicProperty));
        $this->assertFalse(isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * Verifies that accessing a public property containing an array behaves like in a normal context
     */
    public function testCanWriteToArrayKeysInPublicProperty()
    {
        $instance    = new ClassWithPublicArrayProperty();
        $className   = get_class($instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicArrayProperty */
        $proxy       = new $proxyName($instance);

        $proxy->arrayProperty['foo'] = 'bar';

        $this->assertSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = array('tab' => 'taz');

        $this->assertSame(array('tab' => 'taz'), $proxy->arrayProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object state
     */
    public function testWillNotModifyRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = new $proxyName($instance);
        $variable    = $proxy->property0;

        $this->assertSame('property0', $variable);

        $variable = 'foo';

        $this->assertSame('property0', $proxy->property0);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * Verifies that public properties references retrieved via `__get` modify in the object state
     */
    public function testWillModifyByRefRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = new $proxyName($instance);
        $variable    = & $proxy->property0;

        $this->assertSame('property0', $variable);

        $variable = 'foo';

        $this->assertSame('foo', $proxy->property0);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string $parentClassName
     *
     * @return string
     *
     * @throws UnsupportedProxiedClassException
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new AccessInterceptorScopeLocalizerGenerator();
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
                array('param' => new \stdClass()),
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
        $instance1  = new BaseClass();
        $proxyName1 = $this->generateProxy(get_class($instance1));

        return array(
            array(
                $instance1,
                new $proxyName1($instance1),
                'publicProperty',
                'publicPropertyDefault',
            ),
        );
    }

    /**
     * @param object                     $instance
     * @param AccessInterceptorInterface $proxy
     */
    private function assertProxySynchronized($instance, AccessInterceptorInterface $proxy)
    {
        $reflectionClass = new ReflectionClass($instance);

        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);

            $this->assertSame(
                $property->getValue($instance),
                $property->getValue($proxy),
                'Property "' . $property->getName() . '" is synchronized between instance and proxy'
            );
        }
    }
}
