<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\JsonRpc as JsonRpcAdapter;
use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc as XmlRpcAdapter;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Factory\RemoteObjectFactory;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\OtherObjectAccessClass;
use ProxyManagerTestAsset\RemoteProxy\BazServiceInterface;
use ProxyManagerTestAsset\RemoteProxy\Foo;
use ProxyManagerTestAsset\RemoteProxy\FooServiceInterface;
use ProxyManagerTestAsset\RemoteProxy\RemoteServiceWithDefaultsInterface;
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
final class RemoteObjectFunctionalTest extends TestCase
{
    /**
     * @param mixed   $expectedValue
     * @param mixed[] $params
     */
    protected function getXmlRpcAdapter($expectedValue, string $method, array $params) : XmlRpcAdapter
    {
        $client = $this->getMockBuilder(Client::class)->getMock();

        $client
            ->method('call')
            ->with(self::stringEndsWith($method), $params)
            ->willReturn($expectedValue);

        return new XmlRpcAdapter(
            $client,
            ['ProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo']
        );
    }

    /**
     * @param mixed   $expectedValue
     * @param mixed[] $params
     */
    protected function getJsonRpcAdapter($expectedValue, string $method, array $params) : JsonRpcAdapter
    {
        $client = $this->getMockBuilder(Client::class)->getMock();

        $client
            ->method('call')
            ->with(self::stringEndsWith($method), $params)
            ->willReturn($expectedValue);

        return new JsonRpcAdapter(
            $client,
            ['ProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo']
        );
    }

    /**
     * @param string|object $instanceOrClassName
     * @param array|mixed[] $passedParams
     * @param mixed[]       $parametersForProxy
     * @param mixed         $expectedValue
     *
     * @dataProvider getProxyMethods
     *
     * @psalm-template OriginalClass of object
     * @psalm-param class-string<OriginalClass>|OriginalClass $instanceOrClassName
     */
    public function testXmlRpcMethodCalls(
        $instanceOrClassName,
        string $method,
        array $passedParams,
        array $parametersForProxy,
        $expectedValue
    ) : void {
        $proxy = (new RemoteObjectFactory($this->getXmlRpcAdapter($expectedValue, $method, $parametersForProxy)))
            ->createProxy($instanceOrClassName);

        $callback = [$proxy, $method];

        self::assertIsCallable($callback);
        self::assertSame($expectedValue, $callback(...$passedParams));
    }

    /**
     * @param string|object $instanceOrClassName
     * @param array|mixed[] $passedParams
     * @param mixed[]       $parametersForProxy
     * @param mixed         $expectedValue
     *
     * @dataProvider getProxyMethods
     *
     * @psalm-template OriginalClass of object
     * @psalm-param class-string<OriginalClass>|OriginalClass $instanceOrClassName
     */
    public function testJsonRpcMethodCalls(
        $instanceOrClassName,
        string $method,
        array $passedParams,
        array $parametersForProxy,
        $expectedValue
    ) : void {
        $proxy = (new RemoteObjectFactory($this->getJsonRpcAdapter($expectedValue, $method, $parametersForProxy)))
            ->createProxy($instanceOrClassName);

        $callback = [$proxy, $method];

        self::assertIsCallable($callback);
        self::assertSame($expectedValue, $callback(...$passedParams));
    }

    /**
     * @param string|object $instanceOrClassName
     * @param mixed         $propertyValue
     *
     * @dataProvider getPropertyAccessProxies
     *
     * @psalm-template OriginalClass of object
     * @psalm-param class-string<OriginalClass>|OriginalClass $instanceOrClassName
     */
    public function testJsonRpcPropertyReadAccess($instanceOrClassName, string $publicProperty, $propertyValue) : void
    {
        $proxy = (new RemoteObjectFactory($this->getJsonRpcAdapter($propertyValue, '__get', [$publicProperty])))
            ->createProxy($instanceOrClassName);

        self::assertSame($propertyValue, $proxy->{$publicProperty});
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return string[][]|bool[][]|object[][]|mixed[][][]
     */
    public function getProxyMethods() : array
    {
        $selfHintParam = new ClassWithSelfHint();

        return [
            [
                FooServiceInterface::class,
                'foo',
                [],
                [],
                'bar remote',
            ],
            [
                Foo::class,
                'foo',
                [],
                [],
                'bar remote',
            ],
            [
                new Foo(),
                'foo',
                [],
                [],
                'bar remote',
            ],
            [
                BazServiceInterface::class,
                'baz',
                ['baz'],
                ['baz'],
                'baz remote',
            ],
            [
                new ClassWithSelfHint(),
                'selfHintMethod',
                [$selfHintParam],
                [$selfHintParam],
                $selfHintParam,
            ],
            [
                VariadicArgumentsServiceInterface::class,
                'method',
                ['aaa', 1, 2, 3, 4, 5],
                ['aaa', 1, 2, 3, 4, 5],
                true,
            ],
            [
                RemoteServiceWithDefaultsInterface::class,
                'optionalNonNullable',
                ['aaa'],
                ['aaa', ''],
                200,
            ],
            [
                RemoteServiceWithDefaultsInterface::class,
                'optionalNullable',
                ['aaa'],
                ['aaa', null],
                200,
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
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccess(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ) : void {
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(get_class($realInstance), '__get', [$propertyName])
            ->willReturn($expectedValue);

        $proxy = (new RemoteObjectFactory($adapter))
            ->createProxy($realInstance);

        /** @var callable $accessor */
        $accessor = [$callerObject, $method];

        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group        276
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccessEvenIfCloned(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ) : void {
        $adapter = $this->createMock(AdapterInterface::class);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(get_class($realInstance), '__get', [$propertyName])
            ->willReturn($expectedValue);

        $proxy = clone (new RemoteObjectFactory($adapter))
            ->createProxy($realInstance);

        /** @var callable $accessor */
        $accessor = [$callerObject, $method];

        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group 327
     */
    public function testWillExecuteLogicInAVoidMethod() : void
    {
        $adapter = $this->createMock(AdapterInterface::class);

        $increment = random_int(10, 1000);

        $adapter
            ->expects(self::once())
            ->method('call')
            ->with(VoidCounter::class, 'increment', [$increment])
            ->willReturn(random_int(10, 1000));

        $proxy = clone (new RemoteObjectFactory($adapter))
            ->createProxy(VoidCounter::class);

        $proxy->increment($increment);
    }

    public function getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope() : Generator
    {
        foreach ((new ReflectionClass(OtherObjectAccessClass::class))->getProperties() as $property) {
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
