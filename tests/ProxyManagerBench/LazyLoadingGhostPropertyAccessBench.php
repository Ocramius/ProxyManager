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
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use ReflectionProperty;

/**
 * Benchmark that provides results for simple initialization/state access for lazy loading ghost proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @BeforeMethods({"setUp"})
 */
class LazyLoadingGhostPropertyAccessBench
{
    /**
     * @var EmptyClass|GhostObjectInterface
     */
    private $emptyClassProxy;

    /**
     * @var EmptyClass|GhostObjectInterface
     */
    private $initializedEmptyClassProxy;

    /**
     * @var ClassWithPrivateProperties|GhostObjectInterface
     */
    private $privatePropertiesProxy;

    /**
     * @var ClassWithPrivateProperties|GhostObjectInterface
     */
    private $initializedPrivatePropertiesProxy;

    /**
     * @var ReflectionProperty
     */
    private $accessPrivateProperty;

    /**
     * @var ClassWithProtectedProperties|GhostObjectInterface
     */
    private $protectedPropertiesProxy;

    /**
     * @var ClassWithProtectedProperties|GhostObjectInterface
     */
    private $initializedProtectedPropertiesProxy;

    /**
     * @var ReflectionProperty
     */
    private $accessProtectedProperty;

    /**
     * @var ClassWithPublicProperties|GhostObjectInterface
     */
    private $publicPropertiesProxy;

    /**
     * @var ClassWithPublicProperties|GhostObjectInterface
     */
    private $initializedPublicPropertiesProxy;

    /**
     * @var ClassWithMixedProperties|GhostObjectInterface
     */
    private $mixedPropertiesProxy;

    /**
     * @var ClassWithMixedProperties|GhostObjectInterface
     */
    private $initializedMixedPropertiesProxy;

    public function setUp()
    {
        $this->emptyClassProxy          = $this->buildProxy(EmptyClass::class);
        $this->privatePropertiesProxy   = $this->buildProxy(ClassWithPrivateProperties::class);
        $this->protectedPropertiesProxy = $this->buildProxy(ClassWithProtectedProperties::class);
        $this->publicPropertiesProxy    = $this->buildProxy(ClassWithPublicProperties::class);
        $this->mixedPropertiesProxy     = $this->buildProxy(ClassWithMixedProperties::class);

        $this->initializedEmptyClassProxy          = $this->buildProxy(EmptyClass::class);
        $this->initializedPrivatePropertiesProxy   = $this->buildProxy(ClassWithPrivateProperties::class);
        $this->initializedProtectedPropertiesProxy = $this->buildProxy(ClassWithProtectedProperties::class);
        $this->initializedPublicPropertiesProxy    = $this->buildProxy(ClassWithPublicProperties::class);
        $this->initializedMixedPropertiesProxy     = $this->buildProxy(ClassWithMixedProperties::class);

        $this->initializedEmptyClassProxy->initializeProxy();
        $this->initializedPrivatePropertiesProxy->initializeProxy();
        $this->initializedProtectedPropertiesProxy->initializeProxy();
        $this->initializedPublicPropertiesProxy->initializeProxy();
        $this->initializedMixedPropertiesProxy->initializeProxy();

        $this->accessPrivateProperty = new ReflectionProperty(ClassWithPrivateProperties::class, 'property0');
        $this->accessPrivateProperty->setAccessible(true);

        $this->accessProtectedProperty = new ReflectionProperty(ClassWithProtectedProperties::class, 'property0');
        $this->accessProtectedProperty->setAccessible(true);
    }

    public function benchEmptyClassInitialization() : void
    {
        $this->emptyClassProxy->initializeProxy();
    }

    public function benchInitializedEmptyClassInitialization() : void
    {
        $this->initializedEmptyClassProxy->initializeProxy();
    }

    public function benchObjectWithPrivatePropertiesInitialization() : void
    {
        $this->privatePropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithPrivatePropertiesInitialization() : void
    {
        $this->initializedPrivatePropertiesProxy->initializeProxy();
    }

    public function benchObjectWithPrivatePropertiesPropertyRead() : void
    {
        $this->accessPrivateProperty->getValue($this->privatePropertiesProxy);
    }

    public function benchInitializedObjectWithPrivatePropertiesPropertyRead() : void
    {
        $this->accessPrivateProperty->getValue($this->initializedPrivatePropertiesProxy);
    }

    public function benchObjectWithPrivatePropertiesPropertyWrite() : void
    {
        $this->accessPrivateProperty->setValue($this->privatePropertiesProxy, 'foo');
    }

    public function benchInitializedObjectWithPrivatePropertiesPropertyWrite() : void
    {
        $this->accessPrivateProperty->setValue($this->initializedPrivatePropertiesProxy, 'foo');
    }

    public function benchObjectWithProtectedPropertiesInitialization() : void
    {
        $this->protectedPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithProtectedPropertiesInitialization() : void
    {
        $this->initializedProtectedPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithProtectedPropertiesPropertyRead() : void
    {
        $this->accessProtectedProperty->getValue($this->protectedPropertiesProxy);
    }

    public function benchInitializedObjectWithProtectedPropertiesPropertyRead() : void
    {
        $this->accessProtectedProperty->getValue($this->initializedProtectedPropertiesProxy);
    }

    public function benchObjectWithProtectedPropertiesPropertyWrite() : void
    {
        $this->accessProtectedProperty->setValue($this->protectedPropertiesProxy, 'foo');
    }

    public function benchInitializedObjectWithProtectedPropertiesPropertyWrite() : void
    {
        $this->accessProtectedProperty->setValue($this->initializedProtectedPropertiesProxy, 'foo');
    }

    public function benchObjectWithPublicPropertiesInitialization() : void
    {
        $this->publicPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithPublicPropertiesInitialization() : void
    {
        $this->initializedPublicPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithPublicPropertiesPropertyRead() : void
    {
        $this->publicPropertiesProxy->property0;
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyRead() : void
    {
        $this->initializedPublicPropertiesProxy->property0;
    }

    public function benchObjectWithPublicPropertiesPropertyWrite() : void
    {
        $this->publicPropertiesProxy->property0 = 'foo';
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyWrite() : void
    {
        $this->initializedPublicPropertiesProxy->property0 = 'foo';
    }

    public function benchObjectWithPublicPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->publicPropertiesProxy->property0);
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->initializedPublicPropertiesProxy->property0);
    }

    public function benchObjectWithPublicPropertiesPropertyUnset() : void
    {
        unset($this->publicPropertiesProxy->property0);
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyUnset() : void
    {
        unset($this->initializedPublicPropertiesProxy->property0);
    }

    public function benchObjectWithMixedPropertiesInitialization() : void
    {
        $this->mixedPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithMixedPropertiesInitialization() : void
    {
        $this->initializedMixedPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithMixedPropertiesPropertyRead() : void
    {
        $this->mixedPropertiesProxy->publicProperty0;
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyRead() : void
    {
        $this->initializedMixedPropertiesProxy->publicProperty0;
    }

    public function benchObjectWithMixedPropertiesPropertyWrite() : void
    {
        $this->mixedPropertiesProxy->publicProperty0 = 'foo';
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyWrite() : void
    {
        $this->initializedMixedPropertiesProxy->publicProperty0 = 'foo';
    }

    public function benchObjectWithMixedPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->mixedPropertiesProxy->publicProperty0);
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->initializedMixedPropertiesProxy->publicProperty0);
    }

    public function benchObjectWithMixedPropertiesPropertyUnset() : void
    {
        unset($this->mixedPropertiesProxy->publicProperty0);
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyUnset() : void
    {
        unset($this->initializedMixedPropertiesProxy->publicProperty0);
    }

    private function buildProxy(string $originalClass) : LazyLoadingInterface
    {
        return ($this->generateProxyClass($originalClass))::staticProxyConstructor(
            function ($proxy, string $method, $params, & $initializer) : bool {
                $initializer = null;

                return true;
            }
        );
    }

    private function generateProxyClass(string $originalClassName) : string
    {
        $generatedClassName = __CLASS__ . '\\' . $originalClassName;

        if (class_exists($generatedClassName)) {
            return $generatedClassName;
        }

        $generatedClass     = new ClassGenerator($generatedClassName);

        (new LazyLoadingGhostGenerator())->generate(new ReflectionClass($originalClassName), $generatedClass, []);
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        return $generatedClassName;
    }
}
