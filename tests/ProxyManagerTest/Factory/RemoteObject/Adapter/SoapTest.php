<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\Soap;
use Zend\Server\Client;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\Soap}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
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
        /* @var $client Client|\PHPUnit_Framework_MockObject_MockObject */
        $client = $this->getMockBuilder(Client::class)->setMethods(['call'])->getMock();

        $adapter = new Soap($client);

        $client
            ->expects(self::once())
            ->method('call')
            ->with('bar', ['tab' => 'taz'])
            ->will(self::returnValue('baz'));

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
