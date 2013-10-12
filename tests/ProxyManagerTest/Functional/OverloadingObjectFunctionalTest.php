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
use ProxyManager\ProxyGenerator\OverloadingObjectGenerator;
use ReflectionClass;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManagerTestAsset\OverloadingObject\Baz;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\OverloadingObjectGenerator} produced objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class OverloadingObjectFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls($className, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\OverloadingObjectInterface */
        $proxy     = new $proxyName();

        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
    }

    /**
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterCloning($className, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\OverloadingObjectInterface */
        $proxy     = new $proxyName();
        $cloned    = clone $proxy;

        $this->assertSame($expectedValue, call_user_func_array(array($cloned, $method), $params));
    }
    
    /**
     * Verifies that interface is not allowed
     */
    public function testMethodCallsFromInterface()
    {
        $this->setExpectedException('ProxyManager\Proxy\Exception\OverloadingObjectException');
        $proxyName = $this->generateProxy('ProxyManagerTestAsset\\BaseInterface');
    }
    
    public function testOverloadedMethodCallsWithSimpleObject()
    {
        $methods = array(
            'publicMethod' => array(
                function($string) { return 'publicMethodDefault ' . $string; },
                function($string, $otherString) { return 'publicMethodDefault ' . $string . $otherString; },
                function(\stdClass $object) { return 'publicMethodDefault(stdClass)'; },
                function(Baz $baz) { return $baz; },
            ),
            'newMethod' => array(
                function() { return 'newMethod'; },
                function($string) { return 'newMethod' . $string; },
            ),
            'newMethodWithReference' => function(&$foo) { $foo .= ' overloaded'; },
            'newMethodWithParam' => function($string) { return 'newMethodWith' . $string; },
            'readPublicProperty' => function() { return $this->publicProperty; },
            'readProtectedProperty' => function() { return $this->protectedProperty; },
            'readPrivateProperty' => function() { return $this->privateProperty; },
        );
        
        $proxyName = $this->generateProxy('ProxyManagerTestAsset\\BaseClass', $methods);

        /* @var $proxy \ProxyManager\Proxy\OverloadingObjectInterface */
        $proxy = new $proxyName();
        
        $this->assertEquals('publicMethodDefault overloaded', $proxy->publicMethod('overloaded'));
        $this->assertEquals('publicMethodDefault overloaded!', $proxy->publicMethod('overloaded', '!'));
        $this->assertEquals('publicMethodDefault(stdClass)', $proxy->publicMethod(new \stdClass()));
        $this->assertEquals('baz class', $proxy->publicMethod(new Baz()));
        $this->assertEquals('newMethod', $proxy->newMethod());
        $this->assertEquals('newMethod!', $proxy->newMethod('!'));
        $foo = 'newMethodWithReference';
        $proxy->newMethodWithReference(&$foo);
        $this->assertEquals('newMethodWithReference overloaded', $foo);
        $this->assertEquals('newMethodWithParam', $proxy->newMethodWithParam('Param'));
        $this->assertEquals('publicPropertyDefault', $proxy->readPublicProperty());
        $this->assertEquals('protectedPropertyDefault', $proxy->readProtectedProperty());
        $this->assertEquals('privatePropertyDefault', $proxy->readPrivateProperty());
    }
    
    public function testOverloadedMethodCallsWithObjectInterfaceBased()
    {
        $methods = array(
            'bar' => array(
                function($string) { return $string; },
                function(Baz $b, $string) { return $b . $string; },
            ),
            'baz' => array(
                function() { return 'baz default'; },
                function($string, $otherString) { return $string . $otherString; },
            ),
        );
        
        $proxyName = $this->generateProxy('ProxyManagerTestAsset\\OverloadingObject\\Foo', $methods);

        /* @var $proxy \ProxyManager\Proxy\OverloadingObjectInterface */
        $proxy = new $proxyName();
        
        $this->assertEquals('default', $proxy->bar());
        $this->assertEquals('test', $proxy->bar('test'));
        $this->assertEquals('baz class!', $proxy->bar(new Baz(), '!'));
        $this->assertEquals('baz!', $proxy->baz('!'));
        $this->assertEquals('bazzz!', $proxy->baz('bazzz', '!'));
        $this->assertEquals('baz default', $proxy->baz());
    }
    
    public function testOverloadedMethodCallsWithCallableType()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('`callable` is only supported in PHP >=5.4.0');
        }
        
        $methods = array(
            'bar' => array(
                function($string) { return $string; },
                function(callable $callable) { return $callable(); },
            ),
        );
        
        $proxyName = $this->generateProxy('ProxyManagerTestAsset\\OverloadingObject\\Foo', $methods);
        
        /* @var $proxy \ProxyManager\Proxy\OverloadingObjectInterface */
        $proxy = new $proxyName();
        
        $this->assertEquals('callable', $proxy->bar(function() { return 'callable'; }));
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName, array $methods = array())
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new OverloadingObjectGenerator();
        $generator->setDefaultMethods($methods);
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
                'ProxyManagerTestAsset\\BaseClass',
                'publicMethod',
                array(),
                'publicMethodDefault'
            ),
            array(
                'ProxyManagerTestAsset\\BaseClass',
                'publicTypeHintedMethod',
                array('param' => new \stdClass()),
                'publicTypeHintedMethodDefault'
            ),
            array(
                'ProxyManagerTestAsset\\BaseClass',
                'publicByReferenceMethod',
                array(),
                'publicByReferenceMethodDefault'
            ),
        );
    }
}
