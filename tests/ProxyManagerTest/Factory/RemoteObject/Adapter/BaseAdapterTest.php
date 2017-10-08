<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter;
use Zend\Server\Client;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\Soap}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class BaseAdapterTest extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter::call
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testBaseAdapter() : void
    {
        /* @var $client Client|\PHPUnit_Framework_MockObject_MockObject */
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['call'])
            ->getMock();

        $adapter = $this->getMockForAbstractClass(
            BaseAdapter::class,
            [$client]
        );

        $client
            ->expects(self::once())
            ->method('call')
            ->with('foobarbaz', ['tab' => 'taz'])
            ->will(self::returnValue('baz'));

        $adapter
            ->expects(self::once())
            ->method('getServiceName')
            ->with('foo', 'bar')
            ->will(self::returnValue('foobarbaz'));

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter::call
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testBaseAdapterWithServiceMap() : void
    {
        /* @var $client Client|\PHPUnit_Framework_MockObject_MockObject */
        $client = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['call'])
            ->getMock();

        $adapter = $this->getMockForAbstractClass(
            BaseAdapter::class,
            [$client, ['foobarbaz' => 'mapped']]
        );

        $client
            ->expects(self::once())
            ->method('call')
            ->with('mapped', ['tab' => 'taz'])
            ->will(self::returnValue('baz'));

        $adapter
            ->expects(self::once())
            ->method('getServiceName')
            ->with('foo', 'bar')
            ->will(self::returnValue('foobarbaz'));

        self::assertSame('baz', $adapter->call('foo', 'bar', ['tab' => 'taz']));
    }
}
