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
use ProxyManager\Configuration;
use ProxyManager\Factory\OverloadingFactory;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use stdClass;

/**
 * Tests for {@see \ProxyManager\Factory\OverloadingFactory}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class OverloadingFactoryTest extends PHPUnit_Framework_TestCase
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
     * @covers \ProxyManager\Factory\OverloadingFactory::__construct
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxy
     */
    public function testWillSkipAutoGeneration()
    {
        $instance = new stdClass();

        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will($this->returnValue('ProxyManagerTestAsset\\OverloadingObjectMock'));

        $factory    = new OverloadingFactory($this->config);
        /* @var $proxy \ProxyManagerTestAsset\OverloadingObjectMock */
        $proxy      = $factory->createProxy($instance);

        $this->assertInstanceOf('ProxyManagerTestAsset\\OverloadingObjectMock', $proxy);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\OverloadingFactory::__construct
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxy
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration()
    {
        $instance       = new stdClass();
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
                            . ' extends \\ProxyManagerTestAsset\\OverloadingObjectMock {}'
                        );
                    }
                )
            );

        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will($this->returnValue($proxyClassName));

        $this
            ->inflector
            ->expects($this->once())
            ->method('getUserClassName')
            ->with('stdClass')
            ->will($this->returnValue('ProxyManagerTestAsset\\OverloadingObjectMock'));

        $factory    = new OverloadingFactory($this->config);
        /* @var $proxy \ProxyManagerTestAsset\OverloadingObjectMock */
        $proxy      = $factory->createProxy($instance);

        $this->assertInstanceOf($proxyClassName, $proxy);
    }
    
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\OverloadingFactory::__construct
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxy
     */
    public function testCanCreateDefaultProxyMethods()
    {
        $config = new Configuration();
        $config->setProxiesNamespace(Configuration::DEFAULT_PROXY_NAMESPACE . __FUNCTION__);
        
        $factory = new OverloadingFactory($config);
        $proxy = $factory->createProxy('ProxyManagerTestAsset\\OverloadingObjectMock', array(
            'foo' => function($foo) { return $foo; },
            'useThis' => function() { return $self->property; } // $self will create automatically
        ));
        $this->assertEquals('bar', $proxy->foo('bar'));
        $this->assertEquals('propertyDefault', $proxy->useThis());
    }
    
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\OverloadingFactory::__construct
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxy
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxyMethods
     */
    public function testCanCreateProxyMethods()
    {
        $config = new Configuration();
        $config->setProxiesNamespace(Configuration::DEFAULT_PROXY_NAMESPACE . __FUNCTION__);
        
        $factory = new OverloadingFactory($config);
        $proxy = $factory->createProxy('ProxyManagerTestAsset\\OverloadingObjectMock');
        $factory->createProxyMethods($proxy, array(
            'foo' => function($foo) { return $foo; },
            'useThis' => function() use ($proxy) { return $proxy->property; } // use current object
        ));
            
        $this->assertEquals('bar', $proxy->foo('bar'));
        $this->assertEquals('propertyDefault', $proxy->useThis());
    }
    
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\OverloadingFactory::__construct
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxy
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxyMethods
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxyDocumentation
     */
    public function testCanCreateDocumentationBasedExistingClass()
    {
        $config = new Configuration();
        $config->setProxiesNamespace(Configuration::DEFAULT_PROXY_NAMESPACE . __FUNCTION__);
        
        $factory = new OverloadingFactory($config);
        $proxy = $factory->createProxy('ProxyManagerTestAsset\\OverloadingObjectMock');
        
        $documentation = $factory->createProxyDocumentation($proxy);
        $content = 'namespace ProxyManagerTestAsset;

class OverloadingObjectMock
{

    public function function1()
    {
        return \'function1\';
    }

    public function function2($string)
    {
        return \'function2\' . $string;
    }

    public function function3(\ProxyManagerTestAsset\OverloadingObject\Baz $baz)
    {
        return \'function3\' . $baz;
    }

    /**
     * @return string
     */
    public function publicMethod()
    {
        return \'publicMethodDefault\';
    }


}
';
        $this->assertEquals($documentation, $content);
    }
    
    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\OverloadingFactory::__construct
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxy
     * @covers \ProxyManager\Factory\OverloadingFactory::createProxyDocumentation
     */
    public function testCanCreateDocumentationWithClassExtension()
    {
        $config = new Configuration();
        $config->setProxiesNamespace(Configuration::DEFAULT_PROXY_NAMESPACE . __FUNCTION__);
        
        $factory = new OverloadingFactory($config);
        $proxy = $factory->createProxy('ProxyManagerTestAsset\\OverloadingObjectMock', array(
            'bar' => function($bar) { return $bar . '!'; },
        ));
        
        $factory->createProxyMethods($proxy, array(
            'foo' => function($foo) { return $foo; },
        ));
        
        $documentation = $factory->createProxyDocumentation($proxy);
        $content = 'namespace ProxyManagerTestAsset;

class OverloadingObjectMock
{

    public function function1()
    {
        return \'function1\';
    }

    public function function2($string)
    {
        return \'function2\' . $string;
    }

    public function function3(\ProxyManagerTestAsset\OverloadingObject\Baz $baz)
    {
        return \'function3\' . $baz;
    }

    /**
     * @return string
     */
    public function publicMethod()
    {
        return \'publicMethodDefault\';
    }

    /**
     * @param $bar
     */
    public function bar($bar)
    {
        return $bar . \'!\';
    }

    /**
     * @param $foo
     */
    public function foo($foo)
    {
        return $foo;
    }


}
';
        $this->assertEquals($documentation, $content);
    }
}
