<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc;
use Zend\Server\Client;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
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
        /* @var $client Client|\PHPUnit_Framework_MockObject_MockObject */
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
