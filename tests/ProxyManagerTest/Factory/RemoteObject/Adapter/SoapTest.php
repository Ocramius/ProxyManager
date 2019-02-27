<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\Soap;
use Zend\Server\Client;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\Soap}
 *
 * @group Coverage
 */
class SoapTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\Soap::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testCanBuildAdapterWithSoapRpcClient() : void
    {
        /** @var Client|MockObject $client */
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
