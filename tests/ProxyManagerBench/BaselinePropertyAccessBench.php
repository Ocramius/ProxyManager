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
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ReflectionProperty;

/**
 * Benchmark that provides baseline results for simple object state interactions
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @BeforeMethods({"setUp"})
 */
class BaselinePropertyAccessBench
{
    /**
     * @var ClassWithPrivateProperties
     */
    private $privateProperties;

    /**
     * @var ReflectionProperty
     */
    private $accessPrivateProperty;

    /**
     * @var ClassWithProtectedProperties
     */
    private $protectedProperties;

    /**
     * @var ReflectionProperty
     */
    private $accessProtectedProperty;

    /**
     * @var ClassWithPublicProperties
     */
    private $publicProperties;

    /**
     * @var ClassWithMixedProperties
     */
    private $mixedProperties;

    /**
     * @var ReflectionProperty
     */
    private $accessMixedPropertiesPrivate;

    /**
     * @var ReflectionProperty
     */
    private $accessMixedPropertiesProtected;

    public function setUp()
    {
        $this->privateProperties   = new ClassWithPrivateProperties();
        $this->protectedProperties = new ClassWithProtectedProperties();
        $this->publicProperties    = new ClassWithPublicProperties();
        $this->mixedProperties     = new ClassWithMixedProperties();

        $this->accessPrivateProperty = new ReflectionProperty(ClassWithPrivateProperties::class, 'property0');
        $this->accessPrivateProperty->setAccessible(true);

        $this->accessProtectedProperty = new ReflectionProperty(ClassWithProtectedProperties::class, 'property0');
        $this->accessProtectedProperty->setAccessible(true);

        $this->accessMixedPropertiesPrivate = new ReflectionProperty(
            ClassWithMixedProperties::class,
            'privateProperty0'
        );
        $this->accessMixedPropertiesPrivate->setAccessible(true);

        $this->accessMixedPropertiesProtected = new ReflectionProperty(
            ClassWithMixedProperties::class,
            'protectedProperty0'
        );
        $this->accessMixedPropertiesProtected->setAccessible(true);
    }

    public function benchPrivatePropertyRead() : void
    {
        $this->accessPrivateProperty->getValue($this->privateProperties);
    }

    public function benchPrivatePropertyWrite() : void
    {
        $this->accessPrivateProperty->setValue($this->privateProperties, 'foo');
    }

    public function benchProtectedPropertyRead() : void
    {
        $this->accessProtectedProperty->getValue($this->protectedProperties);
    }

    public function benchProtectedPropertyWrite() : void
    {
        $this->accessProtectedProperty->setValue($this->protectedProperties, 'foo');
    }

    public function benchPublicPropertyRead() : void
    {
        $this->publicProperties->property0;
    }

    public function benchPublicPropertyWrite() : void
    {
        $this->publicProperties->property0 = 'foo';
    }

    public function benchMixedPropertiesPrivatePropertyRead() : void
    {
        $this->accessMixedPropertiesPrivate->getValue($this->mixedProperties);
    }

    public function benchMixedPropertiesPrivatePropertyWrite() : void
    {
        $this->accessMixedPropertiesPrivate->setValue($this->mixedProperties, 'foo');
    }

    public function benchMixedPropertiesProtectedPropertyRead() : void
    {
        $this->accessMixedPropertiesProtected->getValue($this->mixedProperties);
    }

    public function benchMixedPropertiesProtectedPropertyWrite() : void
    {
        $this->accessMixedPropertiesProtected->setValue($this->mixedProperties, 'foo');
    }

    public function benchMixedPropertiesPublicPropertyRead() : void
    {
        $this->mixedProperties->publicProperty0;
    }

    public function benchMixedPropertiesPublicPropertyWrite() : void
    {
        $this->mixedProperties->publicProperty0 = 'foo';
    }
}
