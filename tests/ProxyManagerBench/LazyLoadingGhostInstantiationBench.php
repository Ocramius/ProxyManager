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

namespace ProxyManagerBench\Functional;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Benchmark that provides results for simple object instantiation for lazy loading ghost proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @BeforeMethods({"setUp"})
 */
class LazyLoadingGhostInstantiationBench
{
    /**
     * @var string
     */
    private $emptyClassProxy;

    /**
     * @var string
     */
    private $privatePropertiesProxy;

    /**
     * @var string
     */
    private $protectedPropertiesProxy;

    /**
     * @var string
     */
    private $publicPropertiesProxy;

    /**
     * @var string
     */
    private $mixedPropertiesProxy;

    public function setUp()
    {
        $this->emptyClassProxy          = $this->generateProxy(EmptyClass::class);
        $this->privatePropertiesProxy   = $this->generateProxy(ClassWithPrivateProperties::class);
        $this->protectedPropertiesProxy = $this->generateProxy(ClassWithProtectedProperties::class);
        $this->publicPropertiesProxy    = $this->generateProxy(ClassWithPublicProperties::class);
        $this->mixedPropertiesProxy     = $this->generateProxy(ClassWithMixedProperties::class);
    }

    public function benchOriginalConstructorInstantiationOfEmptyObject() : void
    {
        new $this->emptyClassProxy;
    }

    public function benchInstantiationOfEmptyObject() : void
    {
        ($this->emptyClassProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithPrivateProperties() : void
    {
        new $this->privatePropertiesProxy;
    }

    public function benchInstantiationOfObjectWithPrivateProperties() : void
    {
        ($this->privatePropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithProtectedProperties() : void
    {
        new $this->protectedPropertiesProxy;
    }

    public function benchInstantiationOfObjectWithProtectedProperties() : void
    {
        ($this->protectedPropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithPublicProperties() : void
    {
        new $this->publicPropertiesProxy;
    }

    public function benchInstantiationOfObjectWithPublicProperties() : void
    {
        ($this->publicPropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithMixedProperties() : void
    {
        new $this->mixedPropertiesProxy;
    }

    public function benchInstantiationOfObjectWithMixedProperties() : void
    {
        ($this->mixedPropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    private function generateProxy(string $originalClass) : string
    {
        $generatedClassName = __CLASS__ . '\\' . $originalClass;

        if (class_exists($generatedClassName)) {
            return $generatedClassName;
        }

        $generatedClass     = new ClassGenerator($generatedClassName);

        (new LazyLoadingGhostGenerator())->generate(new ReflectionClass($originalClass), $generatedClass, []);
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        return $generatedClassName;
    }
}
