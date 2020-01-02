<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use Laminas\Server\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\Soap;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\Soap}
 *
 * @group Coverage
 */
final class SoapTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\Soap::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testCanBuildAdapterWithSoapRpcClient() : void
    {
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $adapter = new Soap($client);

        $client
            ->expects(self::once())
            ->method('call')
            ->with('bar', ['tab' => 'taz'])
            ->willReturn('baz');

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
