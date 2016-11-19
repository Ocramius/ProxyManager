<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use PHPUnit_Framework_TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObject\Adapter\Soap}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class BaseAdapterTest extends PHPUnit_Framework_TestCase
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
        $client = $this
            ->getMockBuilder('Zend\Server\Client')
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
        $client = $this
            ->getMockBuilder('Zend\Server\Client')
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
