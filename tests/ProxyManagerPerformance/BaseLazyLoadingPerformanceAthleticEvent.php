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

use Athletic\AthleticEvent;
use Athletic\Results\MethodResults;
use Closure;
use ReflectionClass;
use zpt\anno\Annotations;

/**
 * Base performance test logic for lazy loading proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 */
abstract class BaseLazyLoadingPerformanceAthleticEvent extends AthleticEvent
{
    /**
     * @var \Athletic\Factories\MethodResultsFactory
     */
    private $methodResultsFactory;

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string $parentClassName
     *
     * @return string
     */
    abstract protected function generateProxy($parentClassName);

    /**
     * @return array[]
     */
    abstract public function getAccessedProperties();

    /**
     * @return array[]
     */
    abstract public function getAccessedMethods();

    /**
     * @return array[]
     */
    abstract public function getGeneratedClasses();

    /**
     * @iterations 1000
     * @group method-call
     * @dataProvider getAccessedMethods
     *
     * @param object         $instance
     * @param string         $methodName
     * @param mixed[]        $parameters
     *
     * @return void
     */
    protected function call($instance, $methodName, array $parameters)
    {
        call_user_func_array(array($instance, $methodName), $parameters);
    }

    /**
     * @iterations 1000
     * @group property-read
     * @dataProvider getAccessedProperties
     *
     * @param object         $instance
     * @param string         $propertyName
     *
     * @return void
     */
    protected function read($instance, $propertyName)
    {
        $var = $instance->$propertyName;
    }

    /**
     * @iterations 1000
     * @group property-write
     * @dataProvider getAccessedProperties
     *
     * @param object         $instance
     * @param string         $propertyName
     *
     * @return void
     */
    protected function write($instance, $propertyName)
    {
        $instance->$propertyName = 'foo';
    }

    /**
     * @iterations 1000
     * @group property-isset
     * @dataProvider getAccessedProperties
     *
     * @param object         $instance
     * @param string         $propertyName
     *
     * @return void
     */
    protected function doIsset($instance, $propertyName)
    {
        isset($instance->$propertyName);
    }

    /**
     * @iterations 1000
     * @group property-unset
     * @dataProvider getAccessedProperties
     *
     * @param object         $instance
     * @param string         $propertyName
     *
     * @return void
     */
    protected function doUnset($instance, $propertyName)
    {
        unset($instance->$propertyName);
    }

    /**
     * @iterations 1000
     * @group class-instantiation
     * @dataProvider getGeneratedClasses
     *
     * @param string  $className
     * @param Closure $initializer
     *
     * @return void
     */
    protected function instance($className, Closure $initializer = null)
    {
        new $className($initializer);
    }

    /**
     * @param \Athletic\Factories\MethodResultsFactory $methodResultsFactory
     */
    public function setMethodFactory($methodResultsFactory)
    {
        $this->methodResultsFactory = $methodResultsFactory;
    }

    /**
     * @return MethodResults[]
     */
    public function run()
    {
        $classReflector    = new ReflectionClass($this);
        $methodAnnotations = array();

        foreach ($classReflector->getMethods() as $methodReflector) {
            $methodAnnotations[$methodReflector->getName()] = new Annotations($methodReflector);
        }

        $this->classSetUp();

        $results = $this->runBenchmarks($methodAnnotations);

        $this->classTearDown();

        return $results;
    }

    /**
     * @param \zpt\anno\Annotations[] $methods
     *
     * @return \Athletic\Results\MethodResults[]
     */
    protected function runBenchmarks($methods)
    {
        $results = array();

        /* @var $annotations \zpt\anno\Annotations */
        foreach ($methods as $methodName => $annotations) {
            $dataSets = $this->getMethodData($annotations);

            if (isset($annotations['iterations'])) {
                $this->setUp();

                foreach (array_keys($dataSets) as $dataSetKey) {
                    $results[] = $this->runMethodBenchmark(
                        $methodName,
                        $annotations,
                        $dataSetKey
                    );
                }

                $this->tearDown();
            }
        }
        return $results;
    }


    /**
     * @param string                $method
     * @param \zpt\anno\Annotations $annotations
     * @param mixed                 $dataSetKey
     *
     * @return \Athletic\Results\MethodResults
     */
    private function runMethodBenchmark($method, $annotations, $dataSetKey)
    {
        $iterations     = (int) $annotations['iterations'];
        $avgCalibration = $this->getCalibrationTime($iterations, $annotations, $dataSetKey);
        $results        = array();
        $sampleData     = $this->getMethodData($annotations, $dataSetKey);
        $dataSetName    = $sampleData['name'];

        for ($i = 0; $i < $iterations; ++$i) {
            $data        = $this->getMethodData($annotations, $dataSetKey);
            $results[$i] = $this->timeMethod($method, $data['data']) - $avgCalibration;
        }

        $finalResults = $this->methodResultsFactory->create($method, $results, $iterations);

        // @todo this logic is to be handled in athletic itself once data providers are available
        $finalResults->methodName .= ' - ' . $dataSetName;

        if (isset($annotations['group']) === true) {
            $dataSetName = $annotations['group']; // . ' - ' . $dataSetName;
        }

        $finalResults->setGroup($dataSetName);

        if (isset($sampleData['baseline']) === true) {
            $finalResults->setBaseline();
        }

        return $finalResults;
    }


    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return double|float
     */
    private function timeMethod($method, array $parameters)
    {
        $start = microtime(true);

        call_user_func_array(array($this, $method), $parameters);

        return microtime(true) - $start;
    }


    /**
     * @param int                   $iterations
     * @param \zpt\anno\Annotations $annotations
     * @param mixed                 $dataSetKey
     *
     * @return float
     */
    private function getCalibrationTime($iterations, $annotations, $dataSetKey)
    {
        $resultsCalibration = array();

        for ($i = 0; $i < $iterations; ++$i) {
            $data                   = $this->getMethodData($annotations, $dataSetKey);
            $resultsCalibration[$i] = $this->timeMethod('emptyCalibrationMethod', $data['data']);
        }

        return array_sum($resultsCalibration) / count($resultsCalibration);
    }

    /**
     * Empty calibration method - used to compute overhead of an empty method being called
     */
    public function emptyCalibrationMethod()
    {
    }

    /**
     * Retrieves test data (from a data provider) for the given method
     *
     * @param \zpt\anno\Annotations $annotations
     * @param mixed|null            $index
     *
     * @return array[]
     */
    private function getMethodData(Annotations $annotations, $index = null)
    {
        $dataSets = isset($annotations['dataProvider'])
            ? $this->{$annotations['dataProvider']}()
            : array('data' => array(), 'name' => 'default');

        if ($index !== null) {
            return $dataSets[$index];
        }

        return $dataSets;
    }
}
