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

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_TestCase;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\RemoteObjectGenerator;
use ProxyManagerTestAsset\RemoteProxy\Foo;
use ReflectionClass;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerTestAsset\RemoteProxy\Client\LocalHttp;
use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc as XmlRpcAdapter;
use ProxyManager\Factory\RemoteObject\Adapter\JsonRpc as JsonRpcAdapter;
use Zend\XmlRpc\Client as XmlRpcClient;
use Zend\Json\Server\Client as JsonRpcClient;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObjectGenerator} produced objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class RemoteObjectFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc
     */
    protected function getXmlRpcAdapter()
    {
        $adapter = new XmlRpcAdapter(
            new XmlRpcClient('http://127.0.0.1:8080/xmlrpc.php'),
            array('ProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo')
        );
        $adapter->getClient()->setHttpClient(new LocalHttp(__DIR__ . '/../../ProxyManagerTestAsset/RemoteProxy/ServerSide/xmlrpc.php', 'xml-rpc'));
        
        return $adapter;
    }
    
    /**
     * @return \ProxyManager\Factory\RemoteObject\Adapter\JsonRpc
     */
    protected function getJsonRpcAdapter()
    {
        $adapter = new JsonRpcAdapter(
            new JsonRpcClient('http://127.0.0.1:8080/jsonrpc.php'),
            array('ProxyManagerTestAsset\RemoteProxy\Foo.foo' => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.foo')
        );
        $adapter->getClient()->setHttpClient(new LocalHttp(__DIR__ . '/../../ProxyManagerTestAsset/RemoteProxy/ServerSide/jsonrpc.php', 'json-rpc'));
        
        return $adapter;
    }
    
    /**
     * @dataProvider getProxyMethods
     */
    public function testXmlRpcMethodCalls($instanceOrClassname, $method, $params, $expectedValue)
    {
        
        $proxyName = $this->generateProxy($instanceOrClassname);

        /* @var $proxy \ProxyManager\Proxy\RemoteObjectInterface */
        $proxy     = new $proxyName($this->getXmlRpcAdapter());

        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
    }
    
    /**
     * @dataProvider getProxyMethods
     */
    public function testJsonRpcMethodCalls($instanceOrClassname, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($instanceOrClassname);
        
        /* @var $proxy \ProxyManager\Proxy\RemoteObjectInterface */
        $proxy     = new $proxyName($this->getJsonRpcAdapter());

        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
    }
    
    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testJsonRpcPropertyReadAccess($instanceOrClassname, $publicProperty, $propertyValue)
    {
        $proxyName = $this->generateProxy($instanceOrClassname);

        /* @var $proxy \ProxyManager\Proxy\RemoteObjectInterface */
        $proxy     = new $proxyName($this->getJsonRpcAdapter());
        
        /* @var $proxy \ProxyManager\Proxy\NullObjectInterface */
        $this->assertSame($propertyValue, $proxy->$publicProperty);
    }
    
    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string           $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new RemoteObjectGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return array
     */
    public function getProxyMethods()
    {
        return array(
            array(
                'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface',
                'foo',
                array(),
                'bar remote'
            ),
            array(
                'ProxyManagerTestAsset\RemoteProxy\Foo',
                'foo',
                array(),
                'bar remote'
            ),
            array(
                new Foo(),
                'foo',
                array(),
                'bar remote'
            ),
            array(
                'ProxyManagerTestAsset\RemoteProxy\BazServiceInterface',
                'baz',
                array('baz'),
                'baz remote'
            ),
        );
    }
    
    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies()
    {
        return array(
            array(
                'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface',
                'publicProperty',
                'publicProperty remote',
            ),
        );
    }
}
