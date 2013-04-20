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
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 */
class LazyLoadingValueHolderPerformanceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var float time when last capture was started
     */
    private $startTime   = 0;

    /**
     * @var int bytes when last capture was started
     */
    private $startMemory = 0;

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
    public function testProxyInstantiationPerformance($className, array $methods, array $properties)
    {
        $proxyName   = $this->generateProxy($className);
        $iterations  = 20000;
        $instances   = array();
        /* @var $proxies \ProxyManager\Proxy\LazyLoadingInterface[] */
        $proxies     = array();
        $initializer = function (
            & $valueHolder,
            LazyLoadingInterface $proxy,
            $method,
            $params,
            & $initializer
        ) use ($className) {
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
            $proxies[] = new $proxyName($initializer);
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
    public function getTestedClasses()
    {
        return array(
            array('stdClass', array(), array()),
            array('ProxyManagerTestAsset\\BaseClass', array('publicMethod' => array()), array('publicProperty')),
        );
    }

    /**
     * @param string                                     $className
     * @param object[]                                   $instances
     * @param \ProxyManager\Proxy\LazyLoadingInterface[] $proxies
     * @param string                                     $methodName
     * @param array                                      $parameters
     */
    private function profileMethodAccess($className, array $instances, array $proxies, $methodName, array $parameters)
    {
        $iterations = count($instances);

        $this->startCapturing();

        foreach ($instances as $instance) {
            call_user_func_array(array($instance, $methodName), $parameters);
        }

        $baseProfile = $this->endCapturing(
            $iterations . ' calls to ' . $className . '#' . $methodName . ': %fms / %fKb'
        );
        $this->startCapturing();

        foreach ($proxies as $proxy) {
            call_user_func_array(array($proxy, $methodName), $parameters);
        }

        $proxyProfile = $this->endCapturing(
            $iterations . ' calls to proxied ' . $className . '#' . $methodName . ': %fms / %fKb'
        );
        $this->compareProfile($baseProfile, $proxyProfile);
    }

    /**
     * @param string                                     $className
     * @param object[]                                   $instances
     * @param \ProxyManager\Proxy\LazyLoadingInterface[] $proxies
     * @param string                                     $property
     */
    private function profilePropertyWrites($className, array $instances, array $proxies, $property)
    {
        $iterations = count($instances);

        $this->startCapturing();

        foreach ($instances as $instance) {
            $instance->$property = 'foo';
        }

        $baseProfile = $this->endCapturing(
            $iterations . ' writes of ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->startCapturing();

        foreach ($proxies as $proxy) {
            $proxy->$property = 'foo';
        }

        $proxyProfile = $this->endCapturing(
            $iterations . ' writes of proxied ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->compareProfile($baseProfile, $proxyProfile);
    }

    /**
     * @param string                                     $className
     * @param object[]                                   $instances
     * @param \ProxyManager\Proxy\LazyLoadingInterface[] $proxies
     * @param string                                     $property
     */
    private function profilePropertyReads($className, array $instances, array $proxies, $property)
    {
        $iterations = count($instances);

        $this->startCapturing();

        foreach ($instances as $instance) {
            $instance->$property;
        }

        $baseProfile = $this->endCapturing(
            $iterations . ' reads of ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->startCapturing();

        foreach ($proxies as $proxy) {
            $proxy->$property;
        }

        $proxyProfile = $this->endCapturing(
            $iterations . ' reads of proxied ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->compareProfile($baseProfile, $proxyProfile);
    }

    /**
     * @param string                                     $className
     * @param object[]                                   $instances
     * @param \ProxyManager\Proxy\LazyLoadingInterface[] $proxies
     * @param string                                     $property
     */
    private function profilePropertyIsset($className, array $instances, array $proxies, $property)
    {
        $iterations = count($instances);

        $this->startCapturing();

        foreach ($instances as $instance) {
            isset($instance->$property);
        }

        $baseProfile = $this->endCapturing(
            $iterations . ' isset of ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->startCapturing();

        foreach ($proxies as $proxy) {
            isset($proxy->$property);
        }

        $proxyProfile = $this->endCapturing(
            $iterations . ' isset of proxied ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->compareProfile($baseProfile, $proxyProfile);
    }

    /**
     * @param string                                     $className
     * @param object[]                                   $instances
     * @param \ProxyManager\Proxy\LazyLoadingInterface[] $proxies
     * @param string                                     $property
     */
    private function profilePropertyUnset($className, array $instances, array $proxies, $property)
    {
        $iterations = count($instances);

        $this->startCapturing();

        foreach ($instances as $instance) {
            unset($instance->$property);
        }

        $baseProfile = $this->endCapturing(
            $iterations . ' unset of ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->startCapturing();

        foreach ($proxies as $proxy) {
            unset($proxy->$property);
        }

        $proxyProfile = $this->endCapturing(
            $iterations . ' unset of proxied ' . $className . '::' . $property . ': %fms / %fKb'
        );
        $this->compareProfile($baseProfile, $proxyProfile);
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
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }

    /**
     * Start profiler snapshot
     */
    private function startCapturing()
    {
        $this->startMemory = memory_get_usage();
        $this->startTime   = microtime(true);
    }

    /**
     * Echo current profiler output
     *
     * @param string $messageTemplate
     *
     * @return array
     */
    private function endCapturing($messageTemplate)
    {
        $time     = microtime(true) - $this->startTime;
        $memory   = memory_get_usage() - $this->startMemory;

        echo sprintf($messageTemplate, $time, $memory / 1024) . "\n";

        return array(
            'time'   => $time,
            'memory' => $memory
        );
    }

    /**
     * Display comparison between two profiles
     * 
     * @param array $baseProfile
     * @param array $proxyProfile
     */
    private function compareProfile(array $baseProfile, array $proxyProfile)
    {
        $timeOverhead   = ($proxyProfile['time'] / $baseProfile['time']) * 100;
        $memoryOverhead = ($proxyProfile['memory'] / $baseProfile['memory']) * 100;

        echo sprintf('Comparison time / memory: %f%% / %f%%', $timeOverhead, $memoryOverhead) . "\n\n";
    }
}
