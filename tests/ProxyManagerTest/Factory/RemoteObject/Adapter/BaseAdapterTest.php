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

namespace ProxyManagerTest\Factory\RemoteObject\Adapter;

use PHPUnit_Framework_TestCase;
use ProxyManager\Factory\RemoteObject\Adapter\Soap;

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
    public function testBaseAdapter()
    {
        $client = $this
            ->getMockBuilder('Zend\Server\Client')
            ->setMethods(array('call'))
            ->getMock();

        $adapter = $this->getMockForAbstractClass(
            'ProxyManager\\Factory\\RemoteObject\\Adapter\\BaseAdapter',
            array($client)
        );

        $client
            ->expects($this->once())
            ->method('call')
            ->with('foobarbaz', array('tab' => 'taz'))
            ->will($this->returnValue('baz'));

        $adapter
            ->expects($this->once())
            ->method('getServiceName')
            ->with('foo', 'bar')
            ->will($this->returnValue('foobarbaz'));

        $this->assertSame('baz', $adapter->call('foo', 'bar', array('tab' => 'taz')));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter::__construct
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\BaseAdapter::call
     * @covers \ProxyManager\Factory\RemoteObject\Adapter\Soap::getServiceName
     */
    public function testBaseAdapterWithServiceMap()
    {
        $client = $this
            ->getMockBuilder('Zend\Server\Client')
            ->setMethods(array('call'))
            ->getMock();

        $adapter = $this->getMockForAbstractClass(
            'ProxyManager\\Factory\\RemoteObject\\Adapter\\BaseAdapter',
            array($client, array('foobarbaz' => 'mapped'))
        );

        $client
            ->expects($this->once())
            ->method('call')
            ->with('mapped', array('tab' => 'taz'))
            ->will($this->returnValue('baz'));

        $adapter
            ->expects($this->once())
            ->method('getServiceName')
            ->with('foo', 'bar')
            ->will($this->returnValue('foobarbaz'));

        $this->assertSame('baz', $adapter->call('foo', 'bar', array('tab' => 'taz')));
    }
}
