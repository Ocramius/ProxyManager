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

use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\HydratorInterface;
use ProxyManager\ProxyGenerator\HydratorGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\HydratedObject;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 */
class HydratorPerformanceTest extends BasePerformanceTest
{
    /**
     * @dataProvider getTestedClasses
     *
     * @param object                                $instance
     * @param \ProxyManager\Proxy\HydratorInterface $hydrator
     * @param \ReflectionProperty[]                 $properties
     * @param array                                 $data
     */
    public function testHydrationPerformance($instance, HydratorInterface $hydrator, array $properties, array $data)
    {
        $iterations = 20000;
        $className  = get_class($instance);

        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            foreach ($properties as $key => $property) {
                $property->setValue($instance, $data[$key]);
            }
        }

        $base = $this->endCapturing('Baseline hydration: ' . $iterations . ' "' . $className . '": %fms / %fKb');
        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            $hydrator->hydrate($data, $instance);
        }

        $proxy = $this->endCapturing('Proxy hydration: ' . $iterations . ' "' . $className . '": %fms / %fKb');

        $this->compareProfile($base, $proxy);
    }

    /**
     * @dataProvider getTestedClasses
     *
     * @param object                                $instance
     * @param \ProxyManager\Proxy\HydratorInterface $hydrator
     * @param \ReflectionProperty[]                 $properties
     */
    public function testExtractionPerformance($instance, HydratorInterface $hydrator, array $properties)
    {
        $iterations = 20000;
        $className  = get_class($instance);

        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            foreach ($properties as $property) {
                $property->getValue($instance);
            }
        }

        $base = $this->endCapturing('Baseline extraction: ' . $iterations . ' "' . $className . '": %fms / %fKb');
        $this->startCapturing();

        for ($i = 0; $i < $iterations; $i += 1) {
            $hydrator->extract($instance);
        }

        $proxy = $this->endCapturing('Proxy extraction: ' . $iterations . ' "' . $className . '": %fms / %fKb');

        $this->compareProfile($base, $proxy);
    }

    /**
     * @return array
     */
    public function getTestedClasses()
    {
        $data = array();

        $classes = array(
            new stdClass(),
            new BaseClass(),
            new HydratedObject(),
            new ClassWithPrivateProperties(),
            new ClassWithProtectedProperties(),
            new ClassWithPublicProperties(),
            new ClassWithMixedProperties(),
        );

        foreach ($classes as $instance) {
            $definitions = $this->generateHydrator($instance);
            $hydrator    = $definitions['hydrator'];
            $properties  = $definitions['properties'];
            $values      = array();

            foreach ($properties as $name => $property) {
                $values[$name] = $name;
            }

            $data[] = array($instance, $hydrator, $properties, $values);
        }

        return $data;
    }

    /**
     * Generates a proxy for the given class name, and retrieves an instance of it
     *
     * @param object $object
     *
     * @return array
     */
    private function generateHydrator($object)
    {
        $generatedClassName   = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator            = new HydratorGenerator();
        $generatedClass       = new ClassGenerator($generatedClassName);
        $strategy             = new EvaluatingGeneratorStrategy();
        $reflection           = new ReflectionClass($object);
        $reflectionProperties = $reflection->getProperties();
        $properties           = array();
        $accessors            = array();

        $generator->generate($reflection, $generatedClass);
        $strategy->generate($generatedClass);

        foreach ($reflectionProperties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            $properties[$reflectionProperty->getName()] = $reflectionProperty;

            if ($reflectionProperty->isPrivate()) {
                $accessors[$reflectionProperty->getName()] = $reflectionProperty;
            }
        }

        return array(
            'hydrator'   => new $generatedClassName($accessors),
            'properties' => $properties,
        );
    }
}
