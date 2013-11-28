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

/**
 * Base performance test logic for lazy loading proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 * @coversNothing
 */
abstract class BaseLazyLoadingPerformanceTest extends BasePerformanceTest
{
    /**
     * @param string                                     $className
     * @param object[]                                   $instances
     * @param \ProxyManager\Proxy\LazyLoadingInterface[] $proxies
     * @param string                                     $methodName
     * @param array                                      $parameters
     */
    protected function profileMethodAccess($className, array $instances, array $proxies, $methodName, array $parameters)
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
    protected function profilePropertyWrites($className, array $instances, array $proxies, $property)
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
    protected function profilePropertyReads($className, array $instances, array $proxies, $property)
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
    protected function profilePropertyIsset($className, array $instances, array $proxies, $property)
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
    protected function profilePropertyUnset($className, array $instances, array $proxies, $property)
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
     * @param string $parentClassName
     *
     * @return string
     */
    abstract protected function generateProxy($parentClassName);
}
