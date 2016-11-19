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

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\JsonRpc as JsonRpcAdapter;
use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc as XmlRpcAdapter;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\RemoteObjectInterface;
use ProxyManager\ProxyGenerator\RemoteObjectGenerator;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\OtherObjectAccessClass;
use ProxyManagerTestAsset\RemoteProxy\Foo;
use ProxyManagerTestAsset\RemoteProxy\FooServiceInterface;
use ReflectionClass;
use Zend\Server\Client;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObjectGenerator} produced objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class RemoteObjectFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param mixed  $expectedValue
     * @param string $method
     * @param array  $params
     *
     * @return XmlRpcAdapter
     */
    protected function getXmlRpcAdapter($expectedValue, string $method, array $params) : XmlRpcAdapter
    {
        /* @var $client Client|\PHPUnit_Framework_MockObject_MockObject */
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $client
            ->expects(self::any())
            ->method('call')
            ->with(self::stringEndsWith($method), $params)
            ->will(self::returnValue($expectedValue));

        $adapter = new XmlRpcAdapter(
            $client,
            [
                 'ProxyManagerTestAsset\RemoteProxy\Foo.foo'
                     => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo'
            ]
        );

        return $adapter;
    }

    /**
     * @param mixed  $expectedValue
     * @param string $method
     * @param array  $params
     *
     * @return JsonRpcAdapter
     */
    protected function getJsonRpcAdapter($expectedValue, string $method, array $params) : JsonRpcAdapter
    {
        /* @var $client Client|\PHPUnit_Framework_MockObject_MockObject */
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $client
            ->expects(self::any())
            ->method('call')
            ->with(self::stringEndsWith($method), $params)
            ->will(self::returnValue($expectedValue));

        $adapter = new JsonRpcAdapter(
            $client,
            [
                 'ProxyManagerTestAsset\RemoteProxy\Foo.foo'
                    => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo'
            ]
        );

        return $adapter;
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string|object $instanceOrClassName
     * @param string        $method
     * @param mixed[]       $params
     * @param mixed         $expectedValue
     */
    public function testXmlRpcMethodCalls($instanceOrClassName, string $method, array $params, $expectedValue) : void
    {
        $proxyName = $this->generateProxy($instanceOrClassName);

        /* @var $proxy \ProxyManager\Proxy\RemoteObjectInterface */
        $proxy     = $proxyName::staticProxyConstructor($this->getXmlRpcAdapter($expectedValue, $method, $params));

        self::assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string|object $instanceOrClassName
     * @param string        $method
     * @param mixed[]       $params
     * @param mixed         $expectedValue
     */
    public function testJsonRpcMethodCalls($instanceOrClassName, string $method, array $params, $expectedValue) : void
    {
        $proxyName = $this->generateProxy($instanceOrClassName);

        /* @var $proxy \ProxyManager\Proxy\RemoteObjectInterface */
        $proxy     = $proxyName::staticProxyConstructor($this->getJsonRpcAdapter($expectedValue, $method, $params));

        self::assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param string|object $instanceOrClassName
     * @param string        $publicProperty
     * @param string        $propertyValue
     */
    public function testJsonRpcPropertyReadAccess($instanceOrClassName, string $publicProperty, $propertyValue) : void
    {
        $proxyName = $this->generateProxy($instanceOrClassName);

        /* @var $proxy \ProxyManager\Proxy\RemoteObjectInterface */
        $proxy     = $proxyName::staticProxyConstructor(
            $this->getJsonRpcAdapter($propertyValue, '__get', [$publicProperty])
        );

        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        self::assertSame($propertyValue, $proxy->$publicProperty);
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string|object $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName) : string
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new RemoteObjectGenerator();
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
    public function getProxyMethods() : array
    {
        $selfHintParam = new ClassWithSelfHint();

        return [
            [
                'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface',
                'foo',
                [],
                'bar remote'
            ],
            [
                'ProxyManagerTestAsset\RemoteProxy\Foo',
                'foo',
                [],
                'bar remote'
            ],
            [
                new Foo(),
                'foo',
                [],
                'bar remote'
            ],
            [
                'ProxyManagerTestAsset\RemoteProxy\BazServiceInterface',
                'baz',
                ['baz'],
                'baz remote'
            ],
            [
                new ClassWithSelfHint(),
                'selfHintMethod',
                [$selfHintParam],
                $selfHintParam
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies() : array
    {
        return [
            [
                FooServiceInterface::class,
                'publicProperty',
                'publicProperty remote',
            ],
        ];
    }

    /**
     * @group 276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     *
     * @param object $callerObject
     * @param object $realInstance
     * @param string $method
     * @param string $expectedValue
     * @param string $propertyName
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccess(
        $callerObject,
        $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ) {
        $proxyName = $this->generateProxy(get_class($realInstance));

        /* @var $adapter AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(get_class($realInstance), '__get', [$propertyName])
            ->willReturn($expectedValue);

        /* @var $proxy OtherObjectAccessClass|RemoteObjectInterface */
        $proxy = $proxyName::staticProxyConstructor($adapter);

        /* @var $accessor callable */
        $accessor = [$callerObject, $method];

        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group 276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     *
     * @param object $callerObject
     * @param object $realInstance
     * @param string $method
     * @param string $expectedValue
     * @param string $propertyName
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccessEvenIfCloned(
        $callerObject,
        $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ) {
        $proxyName = $this->generateProxy(get_class($realInstance));

        /* @var $adapter AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(get_class($realInstance), '__get', [$propertyName])
            ->willReturn($expectedValue);

        /* @var $proxy OtherObjectAccessClass|RemoteObjectInterface */
        $proxy = clone $proxyName::staticProxyConstructor($adapter);

        /* @var $accessor callable */
        $accessor = [$callerObject, $method];

        self::assertSame($expectedValue, $accessor($proxy));
    }

    public function getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope() : \Generator
    {
        foreach ((new \ReflectionClass(OtherObjectAccessClass::class))->getProperties() as $property) {
            $property->setAccessible(true);

            $propertyName  = $property->getName();
            $realInstance  = new OtherObjectAccessClass();
            $expectedValue = uniqid('', true);

            $property->setValue($realInstance, $expectedValue);

            yield OtherObjectAccessClass::class . '#$' . $propertyName => [
                new OtherObjectAccessClass(),
                $realInstance,
                'get' . ucfirst($propertyName),
                $expectedValue,
                $propertyName,
            ];
        }
    }
}
