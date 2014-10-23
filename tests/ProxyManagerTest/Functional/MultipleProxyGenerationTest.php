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
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ReflectionClass;
use ReflectionProperty;

/**
 * Verifies that proxy factories don't conflict with each other when generating proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @link https://github.com/Ocramius/ProxyManager/issues/10
 *
 * @group Functional
 * @group issue-10
 * @coversNothing
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
        $skipScopeLocalizerTests                = false;
        $ghostProxyFactory                      = new LazyLoadingGhostFactory();
        $virtualProxyFactory                    = new LazyLoadingValueHolderFactory();
        $accessInterceptorFactory               = new AccessInterceptorValueHolderFactory();
        $accessInterceptorScopeLocalizerFactory = new AccessInterceptorScopeLocalizerFactory();
        $initializer                            = function () {
        };

        $reflectionClass = new ReflectionClass($className);

        if ((! method_exists('Closure', 'bind')) && $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE)) {
            $skipScopeLocalizerTests = true;
        }

        $generated = array(
            $ghostProxyFactory->createProxy($className, $initializer),
            $virtualProxyFactory->createProxy($className, $initializer),
            $accessInterceptorFactory->createProxy(new $className()),
        );

        if (! $skipScopeLocalizerTests) {
            $generated[] = $accessInterceptorScopeLocalizerFactory->createProxy(new $className());
        }

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

        if (! $skipScopeLocalizerTests) {
            $this->assertInstanceOf('ProxyManager\Proxy\AccessInterceptorInterface', $generated[3]);
        }
    }

    /**
     * @return string[][]
     */
    public function getTestedClasses()
    {
        $data = array(
            array('ProxyManagerTestAsset\\BaseClass'),
            array('ProxyManagerTestAsset\\ClassWithMagicMethods'),
            array('ProxyManagerTestAsset\\ClassWithFinalMethods'),
            array('ProxyManagerTestAsset\\ClassWithFinalMagicMethods'),
            array('ProxyManagerTestAsset\\ClassWithByRefMagicMethods'),
            array('ProxyManagerTestAsset\\ClassWithMixedProperties'),
            array('ProxyManagerTestAsset\\ClassWithPrivateProperties'),
            array('ProxyManagerTestAsset\\ClassWithProtectedProperties'),
            array('ProxyManagerTestAsset\\ClassWithPublicProperties'),
            array('ProxyManagerTestAsset\\EmptyClass'),
            array('ProxyManagerTestAsset\\HydratedObject'),
        );

        if (PHP_VERSION_ID >= 50401) {
            // PHP < 5.4.1 misbehaves, throwing strict standards, see https://bugs.php.net/bug.php?id=60573
            $data[] = array('ProxyManagerTestAsset\\ClassWithSelfHint');
        }

        return $data;
    }
}
