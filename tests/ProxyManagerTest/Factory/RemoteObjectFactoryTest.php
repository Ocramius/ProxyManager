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

namespace ProxyManagerTest\Factory;

use PHPUnit_Framework_TestCase;
use ProxyManager\Factory\RemoteObjectFactory;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;

/**
 * Tests for {@see \ProxyManager\Factory\RemoteObjectFactory}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class RemoteObjectFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $inflector;

    /**
     * @var \ProxyManager\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->config    = $this->getMock('ProxyManager\\Configuration');
        $this->inflector = $this->getMock('ProxyManager\\Inflector\\ClassNameInflectorInterface');
        $this
            ->config
            ->expects($this->any())
            ->method('getClassNameInflector')
            ->will($this->returnValue($this->inflector));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObjectFactory::__construct
     * @covers \ProxyManager\Factory\RemoteObjectFactory::createProxy
     */
    public function testWillSkipAutoGeneration()
    {
        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('ProxyManagerTestAsset\\BaseInterface')
            ->will($this->returnValue('StdClass'));
        
        $adapter    = $this->getMock('ProxyManager\Factory\RemoteObject\AdapterInterface');
        $factory    = new RemoteObjectFactory($adapter, $this->config);
        /* @var $proxy \StdClass */
        $proxy      = $factory->createProxy('ProxyManagerTestAsset\\BaseInterface');

        $this->assertInstanceOf('stdClass', $proxy);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\RemoteObjectFactory::__construct
     * @covers \ProxyManager\Factory\RemoteObjectFactory::createProxy
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration()
    {
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->getMock('ProxyManager\GeneratorStrategy\\GeneratorStrategyInterface');
        $autoloader     = $this->getMock('ProxyManager\\Autoloader\\AutoloaderInterface');

        $this->config->expects($this->any())->method('getGeneratorStrategy')->will($this->returnValue($generator));
        $this->config->expects($this->any())->method('getProxyAutoloader')->will($this->returnValue($autoloader));

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->callback(
                    function (ClassGenerator $targetClass) use ($proxyClassName) {
                        return $targetClass->getName() === $proxyClassName;
                    }
                )
            );

        // simulate autoloading
        $autoloader
            ->expects($this->once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->will(
                $this->returnCallback(
                    function () use ($proxyClassName) {
                        eval(
                            'class ' . $proxyClassName
                            . ' extends stdClass {}'
                        );
                    }
                )
            );

        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('ProxyManagerTestAsset\\BaseInterface')
            ->will($this->returnValue($proxyClassName));

        $this
            ->inflector
            ->expects($this->once())
            ->method('getUserClassName')
            ->with('ProxyManagerTestAsset\\BaseInterface')
            ->will($this->returnValue('stdClass'));

        $adapter    = $this->getMock('ProxyManager\Factory\RemoteObject\AdapterInterface');
        $factory    = new RemoteObjectFactory($adapter, $this->config);
        /* @var $proxy \stdClass */
        $proxy      = $factory->createProxy('ProxyManagerTestAsset\\BaseInterface');

        $this->assertInstanceOf($proxyClassName, $proxy);
    }
}
