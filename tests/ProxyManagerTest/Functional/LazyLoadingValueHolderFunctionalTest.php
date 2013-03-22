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

namespace ProxyManagerTest;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use Closure;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use ProxyManager\Configuration;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerTestAsset\BaseClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class LazyLoadingValueHolderFunctionalTest extends PHPUnit_Framework_TestCase
{
    public function testMethodCalls()
    {
        $instance = new BaseClass();

        $proxyName = $this->generateProxy(get_class($instance));

        /* @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|BaseClass */
        $proxy = new $proxyName($this->createInitializer($instance));

        $this->assertFalse($proxy->isProxyInitialized());

        $this->assertSame('publicMethodDefault', $proxy->publicMethod());

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($instance, $proxy->getWrappedValueHolderValue());

        $this->markTestIncomplete('Needs a data provider to check all methods');
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param  string $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\Foo' . uniqid();
        $generator          = new LazyLoadingValueHolderGenerator();
        $generatedClass     = new PhpClass($generatedClassName);
        $strategy           = new DefaultGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        eval($strategy->generate($generatedClass));

        return $generatedClassName;
    }

    /**
     * @param object $realInstance
     * @param Mock   $initializerMatcher
     *
     * @return \Closure
     */
    private function createInitializer($realInstance, Mock $initializerMatcher = null)
    {
        $initializerMatcher = $initializerMatcher ?: $this->getMock('stdClass', array('__invoke'));

        return function (
            LazyLoadingInterface $proxy,
            & $wrappedObject,
            $method,
            $params
        ) use (
            $initializerMatcher,
            $realInstance
        ) {
            $proxy->setProxyInitializer(null);

            $wrappedObject = $realInstance;

            $initializerMatcher->__invoke($proxy, $wrappedObject, $method, $params);
        };
    }
}
