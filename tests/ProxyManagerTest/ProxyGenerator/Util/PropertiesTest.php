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

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\Properties}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\Properties
 * @group Coverage
 */
class PropertiesTest extends PHPUnit_Framework_TestCase
{
    public function testGetPublicProperties()
    {
        $properties       = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));
        $publicProperties = $properties->getPublicProperties();

        $this->assertCount(3, $publicProperties);
        $this->assertInstanceOf(ReflectionProperty::class, $publicProperties['publicProperty0']);
        $this->assertInstanceOf(ReflectionProperty::class, $publicProperties['publicProperty1']);
        $this->assertInstanceOf(ReflectionProperty::class, $publicProperties['publicProperty2']);
    }

    public function testGetPublicPropertiesSkipsAbstractMethods()
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithAbstractPublicMethod::class));

        $this->assertEmpty($properties->getPublicProperties());
    }

    public function testGetProtectedProperties()
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));

        $protectedProperties = $properties->getProtectedProperties();

        $this->assertCount(3, $protectedProperties);

        $this->assertInstanceOf(ReflectionProperty::class, $protectedProperties["\0*\0protectedProperty0"]);
        $this->assertInstanceOf(ReflectionProperty::class, $protectedProperties["\0*\0protectedProperty1"]);
        $this->assertInstanceOf(ReflectionProperty::class, $protectedProperties["\0*\0protectedProperty2"]);
    }

    public function testGetProtectedPropertiesSkipsAbstractMethods()
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithAbstractProtectedMethod::class));

        $this->assertEmpty($properties->getProtectedProperties());
    }

    public function testGetPrivateProperties()
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));

        $privateProperties = $properties->getPrivateProperties();

        $this->assertCount(3, $privateProperties);

        $prefix = "\0" . ClassWithMixedProperties::class . "\0";

        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'privateProperty0']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'privateProperty1']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'privateProperty2']);
    }

    public function testGetPrivatePropertiesFromInheritance()
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithCollidingPrivateInheritedProperties::class)
        );

        $privateProperties = $properties->getPrivateProperties();

        $this->assertCount(11, $privateProperties);

        $prefix = "\0" . ClassWithCollidingPrivateInheritedProperties::class . "\0";

        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property0']);

        $prefix = "\0" . ClassWithPrivateProperties::class . "\0";

        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property0']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property1']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property2']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property3']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property4']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property5']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property6']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property7']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property8']);
        $this->assertInstanceOf(ReflectionProperty::class, $privateProperties[$prefix . 'property9']);
    }

    public function testGetAccessibleMethods()
    {
        $properties           = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));
        $accessibleProperties = $properties->getAccessibleProperties();

        $this->assertCount(6, $accessibleProperties);
        $this->assertInstanceOf(ReflectionProperty::class, $accessibleProperties['publicProperty0']);
        $this->assertInstanceOf(ReflectionProperty::class, $accessibleProperties['publicProperty1']);
        $this->assertInstanceOf(ReflectionProperty::class, $accessibleProperties['publicProperty2']);
        $this->assertInstanceOf(ReflectionProperty::class, $accessibleProperties["\0*\0protectedProperty0"]);
        $this->assertInstanceOf(ReflectionProperty::class, $accessibleProperties["\0*\0protectedProperty1"]);
        $this->assertInstanceOf(ReflectionProperty::class, $accessibleProperties["\0*\0protectedProperty2"]);
    }

    public function testGetGroupedPrivateProperties()
    {
        $properties     = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));
        $groupedPrivate = $properties->getGroupedPrivateProperties();

        $this->assertCount(1, $groupedPrivate);

        $group = $groupedPrivate[ClassWithMixedProperties::class];

        $this->assertCount(3, $group);

        $this->assertInstanceOf(ReflectionProperty::class, $group['privateProperty0']);
        $this->assertInstanceOf(ReflectionProperty::class, $group['privateProperty1']);
        $this->assertInstanceOf(ReflectionProperty::class, $group['privateProperty2']);
    }

    public function testGetGroupedPrivatePropertiesWithInheritedProperties()
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithCollidingPrivateInheritedProperties::class)
        );

        $groupedPrivate = $properties->getGroupedPrivateProperties();

        $this->assertCount(2, $groupedPrivate);

        $group1 = $groupedPrivate[ClassWithCollidingPrivateInheritedProperties::class];
        $group2 = $groupedPrivate[ClassWithPrivateProperties::class];

        $this->assertCount(1, $group1);
        $this->assertCount(10, $group2);

        $this->assertInstanceOf(ReflectionProperty::class, $group1['property0']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property0']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property1']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property2']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property3']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property4']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property5']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property6']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property7']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property8']);
        $this->assertInstanceOf(ReflectionProperty::class, $group2['property9']);
    }

    public function testGetInstanceProperties()
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        $this->assertCount(9, $properties->getInstanceProperties());
    }

    /**
     * @dataProvider propertiesToSkipFixture
     *
     * @param string $propertyName with property name
     */
    public function testSkipPropertiesByFiltering($propertyName)
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        $this->assertArrayHasKey($propertyName, $properties->getInstanceProperties());
        $filteredProperties =  $properties->filter([$propertyName]);

        $this->assertArrayNotHasKey($propertyName, $filteredProperties->getInstanceProperties());
    }

    public function testSkipOverwritedPropertyUsingInheritance()
    {
        $propertyName = "\0ProxyManagerTestAsset\\ClassWithCollidingPrivateInheritedProperties\0property0";

        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithCollidingPrivateInheritedProperties::class)
        );

        $this->assertArrayHasKey($propertyName, $properties->getInstanceProperties());
        $filteredProperties =  $properties->filter([$propertyName]);

        $this->assertArrayNotHasKey($propertyName, $filteredProperties->getInstanceProperties());
    }

    public function testPropertiesIsSkippedFromRelatedMethods()
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        $this->assertArrayHasKey("\0*\0protectedProperty0", $properties->getProtectedProperties());
        $this->assertArrayHasKey("\0*\0protectedProperty0", $properties->getInstanceProperties());
        $filteredProperties =  $properties->filter(["\0*\0protectedProperty0"]);

        $this->assertArrayNotHasKey("\0*\0protectedProperty0", $filteredProperties->getProtectedProperties());
        $this->assertArrayNotHasKey("\0*\0protectedProperty0", $filteredProperties->getInstanceProperties());
    }

    public function propertiesToSkipFixture()
    {
        return [
            ['publicProperty0'],
            ["\0*\0protectedProperty0"],
            ["\0ProxyManagerTestAsset\\ClassWithMixedProperties\0privateProperty0"],
        ];
    }
}
