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

namespace ProxyManagerPerformance;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManagerTestAsset\BaseClass;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 */
class LazyLoadingGhostPerformanceEvent extends BaseLazyLoadingPerformanceAthleticEvent
{
    /**
     * @var LazyLoadingGhostFactory
     */
    private $factory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $configuration = new Configuration();

        $configuration->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

        $this->factory = new LazyLoadingGhostFactory($configuration);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessedMethods()
    {
        $methods = $this->buildAccessedMethodsArray(array(
            'ProxyManagerTestAsset\\BaseClass'
        ));

        $methods[0]['baseline'] = true;

        return $methods;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessedProperties()
    {
        $properties = $this->buildAccessedPropertiesArray(array(
             // 'stdClass', @todo can't test `stdClass` since PHP segfaults on reflection
             'ProxyManagerTestAsset\\BaseClass',
             'ProxyManagerTestAsset\\ClassWithMixedProperties',
             'ProxyManagerTestAsset\\ClassWithPublicProperties',
        ));

        $properties[0]['baseline'] = true;

        return $properties;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeneratedClasses()
    {
        $classes = $this->buildGeneratedClassesArray(array(
             'stdClass',
             'ProxyManagerTestAsset\\BaseClass',
             'ProxyManagerTestAsset\\ClassWithMixedProperties',
             'ProxyManagerTestAsset\\ClassWithPublicProperties',
        ));

        $classes[0]['baseline'] = true;

        return $classes;
    }

    private function buildAccessedMethodsArray(array $initialClasses)
    {
        $classes = array();

        foreach ($initialClasses as $className) {
            $reflection = new ReflectionClass($className);

            foreach ($reflection->getMethods() as $method) {
                if ($method->isStatic() || ! $method->isPublic() || count($method->getParameters())) {
                    continue;
                }

                $instance         = new $className();
                $initializedProxy = $this->factory->createProxy($className, $this->buildInitializer($instance));

                $initializedProxy->initializeProxy();

                $classes[] = array(
                    'name'     => $reflection->getShortName(),
                    'data'     => array(
                        $instance,
                        $method->getName(),
                        array(),
                    ),
                );
                $classes[] = array(
                    'name' => $reflection->getShortName() . ' (p)',
                    'data' => array(
                        $this->factory->createProxy($className, $this->buildInitializer($instance)),
                        $method->getName(),
                        array(),
                    ),
                );
                $classes[] = array(
                    'name' => $reflection->getShortName() . ' (pi)',
                    'data' => array(
                        $initializedProxy,
                        $method->getName(),
                        array(),
                    ),
                );

                break; // a single method is enough!
            }
        }

        return $classes;
    }

    private function buildAccessedPropertiesArray(array $initialClasses)
    {
        $classes = array();

        foreach ($initialClasses as $className) {
            $reflection = new ReflectionClass($className);

            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic() || ! $property->isPublic()) {
                    continue;
                }

                $instance         = new $className();
                $initializedProxy = $this->factory->createProxy($className, $this->buildInitializer($instance));

                $initializedProxy->initializeProxy();

                $classes[] = array(
                    'name'     => $reflection->getShortName(),
                    'data'     => array(
                        $instance,
                        $property->getName(),
                    ),
                );
                $classes[] = array(
                    'name' => $reflection->getShortName() . ' (p)',
                    'data' => array(
                        $this->factory->createProxy($className, $this->buildInitializer($instance)),
                        $property->getName(),
                    ),
                );
                $classes[] = array(
                    'name' => $reflection->getShortName() . ' (pi)',
                    'data' => array(
                        $initializedProxy,
                        $property->getName(),
                    ),
                );

                break; // a single property is enough!
            }
        }

        return $classes;
    }

    private function buildGeneratedClassesArray(array $initialClasses)
    {
        $classes = array();

        foreach ($initialClasses as $className) {
            $reflection = new ReflectionClass($className);

            $classes[] = array(
                'name' => $reflection->getShortName(),
                'data' => array(
                    $className,
                    function () {
                    }
                ),
            );
            $classes[] = array(
                'name' => $reflection->getShortName() . ' (p)',
                'data' => array(
                    $this->generateProxy($className),
                    function () {
                    }
                ),
            );
        }

        return $classes;
    }

    private function buildInitializer($realInstance)
    {
        /* @var $reflectionProperties ReflectionProperty[] */
        $reflection            = new ReflectionClass($realInstance);
        $reflectionProperties = array_map(
            function (ReflectionProperty $reflectionProperty) {
                $reflectionProperty->setAccessible(true);

                return $reflectionProperty;
            },
            $reflection->getProperties()
        );

        return function ($proxy, $method, $params, & $initializer) use ($reflectionProperties, $realInstance) {
            $initializer = null;

            foreach ($reflectionProperties as $reflectionProperty) {
                $reflectionProperty->setValue($proxy, $reflectionProperty->getValue($realInstance));
            }

            return true;
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function generateProxy($parentClassName)
    {
        return get_class(
            $this->factory->createProxy(
                $parentClassName,
                function () {
                }
            )
        );
    }
}
