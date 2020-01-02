<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use Laminas\Server\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\JsonRpc;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\JsonRpc}
 *
 * @group Coverage
 */
final class JsonRpcTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\JsonRpc::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\JsonRpc::getServiceName
     */
    public function testCanBuildAdapterWithJsonRpcClient() : void
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $adapter = new JsonRpc($client);

        $client
            ->expects(self::once())
            ->method('call')
            ->with('foo.bar', ['tab' => 'taz'])
            ->willReturn('baz');

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
