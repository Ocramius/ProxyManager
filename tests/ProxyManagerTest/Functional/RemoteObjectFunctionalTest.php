<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
use ProxyManagerTestAsset\RemoteProxy\BazServiceInterface;
use ProxyManagerTestAsset\RemoteProxy\Foo;
use ProxyManagerTestAsset\RemoteProxy\FooServiceInterface;
use ProxyManagerTestAsset\RemoteProxy\VariadicArgumentsServiceInterface;
use ProxyManagerTestAsset\VoidCounter;
use ReflectionClass;
use Zend\Server\Client;
use function get_class;
use function random_int;
use function ucfirst;
use function uniqid;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObjectGenerator} produced objects
 *
 * @group Functional
 * @coversNothing
 */
class RemoteObjectFunctionalTest extends TestCase
{
    /**
     * @param mixed   $expectedValue
     * @param mixed[] $params
     */
    protected function getXmlRpcAdapter($expectedValue, string $method, array $params) : XmlRpcAdapter
    {
        /** @var Client|MockObject $client_Framework_MockObject_MockObject */
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $client
            ->expects(self::any())
            ->method('call')
            ->with(self::stringEndsWith($method), $params)
            ->will(self::returnValue($expectedValue));

        $adapter = new XmlRpcAdapter(
            $client,
            ['ProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo']
        );

        return $adapter;
    }

    /**
     * @param mixed   $expectedValue
     * @param mixed[] $params
     */
    protected function getJsonRpcAdapter($expectedValue, string $method, array $params) : JsonRpcAdapter
    {
        /** @var Client|MockObject $client_Framework_MockObject_MockObject */
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $client
            ->expects(self::any())
            ->method('call')
            ->with(self::stringEndsWith($method), $params)
            ->will(self::returnValue($expectedValue));

        $adapter = new JsonRpcAdapter(
            $client,
            ['ProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo']
        );

        return $adapter;
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string|object $instanceOrClassName
     * @param mixed[]       $params
     * @param mixed         $expectedValue
     */
    public function testXmlRpcMethodCalls($instanceOrClassName, string $method, array $params, $expectedValue) : void
    {
        $proxyName = $this->generateProxy($instanceOrClassName);

        /** @var RemoteObjectInterface $proxy */
        $proxy    = $proxyName::staticProxyConstructor($this->getXmlRpcAdapter($expectedValue, $method, $params));
        $callback = [$proxy, $method];

        self::assertInternalType('callable', $callback);
        self::assertSame($expectedValue, $callback(...$params));
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string|object $instanceOrClassName
     * @param mixed[]       $params
     * @param mixed         $expectedValue
     */
    public function testJsonRpcMethodCalls($instanceOrClassName, string $method, array $params, $expectedValue) : void
    {
        $proxyName = $this->generateProxy($instanceOrClassName);

        /** @var RemoteObjectInterface $proxy */
        $proxy    = $proxyName::staticProxyConstructor($this->getJsonRpcAdapter($expectedValue, $method, $params));
        $callback = [$proxy, $method];

        self::assertInternalType('callable', $callback);
        self::assertSame($expectedValue, $callback(...$params));
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param string|object $instanceOrClassName
     */
    public function testJsonRpcPropertyReadAccess($instanceOrClassName, string $publicProperty, $propertyValue) : void
    {
        $proxyName = $this->generateProxy($instanceOrClassName);

        /** @var RemoteObjectInterface $proxy */
        $proxy = $proxyName::staticProxyConstructor(
            $this->getJsonRpcAdapter($propertyValue, '__get', [$publicProperty])
        );

        self::assertSame($propertyValue, $proxy->$publicProperty);
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string|object $parentClassName
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
     * @return string[][]|object[][]|mixed[][][]
     */
    public function getProxyMethods() : array
    {
        $selfHintParam = new ClassWithSelfHint();

        return [
            [
                FooServiceInterface::class,
                'foo',
                [],
                'bar remote',
            ],
            [
                Foo::class,
                'foo',
                [],
                'bar remote',
            ],
            [
                new Foo(),
                'foo',
                [],
                'bar remote',
            ],
            [
                BazServiceInterface::class,
                'baz',
                ['baz'],
                'baz remote',
            ],
            [
                new ClassWithSelfHint(),
                'selfHintMethod',
                [$selfHintParam],
                $selfHintParam,
            ],
            [
                VariadicArgumentsServiceInterface::class,
                'method',
                ['aaa', 1, 2, 3, 4, 5],
                true,
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return string[][]
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
     * @group        276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccess(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ) : void {
        $proxyName = $this->generateProxy(get_class($realInstance));

        /** @var AdapterInterface|MockObject $adapter */
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(get_class($realInstance), '__get', [$propertyName])
            ->willReturn($expectedValue);

        /** @var OtherObjectAccessClass|RemoteObjectInterface $proxy */
        $proxy = $proxyName::staticProxyConstructor($adapter);

        /** @var callable $accessor */
        $accessor = [$callerObject, $method];

        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group        276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccessEvenIfCloned(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ) : void {
        $proxyName = $this->generateProxy(get_class($realInstance));

        /** @var AdapterInterface|MockObject $adapter */
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(get_class($realInstance), '__get', [$propertyName])
            ->willReturn($expectedValue);

        /** @var OtherObjectAccessClass|RemoteObjectInterface $proxy */
        $proxy = clone $proxyName::staticProxyConstructor($adapter);

        /** @var callable $accessor */
        $accessor = [$callerObject, $method];

        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group 327
     */
    public function testWillExecuteLogicInAVoidMethod() : void
    {
        $proxyName = $this->generateProxy(VoidCounter::class);

        /** @var AdapterInterface|MockObject $adapter */
        $adapter = $this->createMock(AdapterInterface::class);

        $increment = random_int(10, 1000);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(VoidCounter::class, 'increment', [$increment])
            ->willReturn(random_int(10, 1000));

        /** @var VoidCounter $proxy */
        $proxy = clone $proxyName::staticProxyConstructor($adapter);

        $proxy->increment($increment);
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
