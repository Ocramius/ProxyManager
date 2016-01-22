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

namespace ProxyManagerTest\Functional;

use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerTestAsset\BaseClass;
use ReflectionClass;
use stdClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 * @coversNothing
 */
class LazyLoadingValueHolderPerformanceTest extends BaseLazyLoadingPerformanceTest
{
    /**
     * @outputBuffering
     * @dataProvider getTestedClasses
     *
     * @param string $className
     * @param array  $methods
     * @param array  $properties
     *
     * @return void
     */
    public function testProxyInstantiationPerformance(string $className, array $methods, array $properties)
    {
        $proxyName   = $this->generateProxy($className);
        $iterations  = 20000;
        $instances   = [];
        /* @var $proxies \ProxyManager\Proxy\VirtualProxyInterface[] */
        $proxies     = [];
        $initializer = function (
            & $valueHolder,
            VirtualProxyInterface $proxy,
            string $method,
            $params,
            & $initializer
        ) use ($className) : bool {
            $initializer = null;
            $valueHolder = new $className();

            return true;
        };

        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            $instances[] = new $className();
        }

        $baseProfile = $this->endCapturing(
            'Instantiation for ' . $iterations . ' objects of type ' . $className . ': %fms / %fKb'
        );
        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            $proxies[] = $proxyName::staticProxyConstructor($initializer);
        }

        $proxyProfile = $this->endCapturing(
            'Instantiation for ' . $iterations . ' proxies of type ' . $className . ': %fms / %fKb'
        );
        $this->compareProfile($baseProfile, $proxyProfile);
        $this->startCapturing();

        foreach ($proxies as $proxy) {
            $proxy->initializeProxy();
        }

        $this->endCapturing('Initialization of ' . $iterations . ' proxies of type ' . $className . ': %fms / %fKb');

        foreach ($methods as $methodName => $parameters) {
            $this->profileMethodAccess($className, $instances, $proxies, $methodName, $parameters);
        }

        foreach ($properties as $property) {
            $this->profilePropertyWrites($className, $instances, $proxies, $property);
            $this->profilePropertyReads($className, $instances, $proxies, $property);
            $this->profilePropertyIsset($className, $instances, $proxies, $property);
            $this->profilePropertyUnset($className, $instances, $proxies, $property);
        }
    }

    /**
     * @return array
     */
    public function getTestedClasses() : array
    {
        return [
            [stdClass::class, [], []],
            [BaseClass::class, ['publicMethod' => []], ['publicProperty']],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function generateProxy(string $parentClassName) : string
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new LazyLoadingValueHolderGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }
}
