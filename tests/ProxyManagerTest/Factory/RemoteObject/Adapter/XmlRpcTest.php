<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use Laminas\Server\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc}
 *
 * @group Coverage
 */
final class XmlRpcTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc::getServiceName
     */
    public function testCanBuildAdapterWithXmlRpcClient() : void
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $adapter = new XmlRpc($client);

        $client
            ->expects(self::once())
            ->method('call')
            ->with('foo.bar', ['taz'])
            ->willReturn('baz');

        self::assertSame('baz', $adapter->call('foo', 'bar', ['taz']));
    }
}
