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
use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Factory\HydratorFactory;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * Verifies that proxy factories don't conflict with each other when generating proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @group issue-10
 */
class MultipleProxyGenerationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Verifies that proxies generated from different factories will retain their specific implementation
     * and won't conflict
     *
     * @dataProvider getTestedClasses
     */
    public function testCanGenerateMultipleDifferentProxiesForSameClass($className)
    {
        $config = new Configuration();

        $ghostProxyFactory        = new LazyLoadingGhostFactory($config);
        $virtualProxyFactory      = new LazyLoadingValueHolderFactory($config);
        $accessInterceptorFactory = new AccessInterceptorValueHolderFactory($config);
        $hydratorFactory          = new HydratorFactory($config);
        $initializer              = function () {
        };

        $generated = array(
            $ghostProxyFactory->createProxy($className, $initializer),
            $virtualProxyFactory->createProxy($className, $initializer),
            $accessInterceptorFactory->createProxy(new $className()),
            $hydratorFactory->createProxy($className),
        );

        foreach ($generated as $key => $proxy) {
            $this->assertInstanceOf($className, $proxy);

            foreach ($generated as $comparedKey => $comparedProxy) {
                if ($comparedKey === $key) {
                    continue;
                }

                $this->assertNotSame(get_class($comparedProxy), get_class($proxy));
            }
        }

        $this->assertInstanceOf('ProxyManager\Proxy\GhostObjectInterface', $generated[0]);
        $this->assertInstanceOf('ProxyManager\Proxy\VirtualProxyInterface', $generated[1]);
        $this->assertInstanceOf('ProxyManager\Proxy\AccessInterceptorInterface', $generated[2]);
        $this->assertInstanceOf('ProxyManager\Proxy\ValueHolderInterface', $generated[2]);
        $this->assertInstanceOf('Zend\Stdlib\Hydrator\HydratorInterface', $generated[3]);
    }

    /**
     * @return string[][]
     */
    public function getTestedClasses()
    {
        return array(
            array('ProxyManagerTestAsset\\BaseClass'),
            array('ProxyManagerTestAsset\\ClassWithMagicMethods'),
            array('ProxyManagerTestAsset\\ClassWithByRefMagicMethods'),
            array('ProxyManagerTestAsset\\ClassWithMixedProperties'),
            array('ProxyManagerTestAsset\\ClassWithPrivateProperties'),
            array('ProxyManagerTestAsset\\ClassWithProtectedProperties'),
            array('ProxyManagerTestAsset\\ClassWithPublicProperties'),
            array('ProxyManagerTestAsset\\EmptyClass'),
            array('ProxyManagerTestAsset\\HydratedObject'),
            array('ProxyManagerTestAsset\\HydratedObject'),
        );
    }
}
