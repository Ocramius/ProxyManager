<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc;
use Zend\Server\Client;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc}
 *
 * @group Coverage
 */
class XmlRpcTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc::getServiceName
     */
    public function testCanBuildAdapterWithXmlRpcClient() : void
    {
        /** @var Client|PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $adapter = new XmlRpc($client);

        $client
            ->expects(self::once())
            ->method('call')
            ->with('foo.bar', ['tab' => 'taz'])
            ->will(self::returnValue('baz'));

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
