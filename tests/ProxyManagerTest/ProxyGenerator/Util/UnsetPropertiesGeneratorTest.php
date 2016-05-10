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

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\EmptyClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator
 * @group Coverage
 */
class UnsetPropertiesGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider classNamesProvider
     *
     * @param string $className
     * @param string $expectedCode
     * @param string $instanceName
     */
    public function testGeneratedCode(string $className, string $expectedCode, string $instanceName)
    {
        self::assertSame(
            $expectedCode,
            UnsetPropertiesGenerator::generateSnippet(
                Properties::fromReflectionClass(new \ReflectionClass($className)),
                $instanceName
            )
        );
    }

    /**
     * @return string[][]
     */
    public function classNamesProvider() : array
    {
        return [
            EmptyClass::class => [
                EmptyClass::class,
                '',
                'foo',
            ],
            BaseClass::class => [
                BaseClass::class,
                'unset($foo->publicProperty, $foo->protectedProperty);

\Closure::bind(function (\ProxyManagerTestAsset\BaseClass $instance) {
    unset($instance->privateProperty);
}, $foo, \'ProxyManagerTestAsset\\\\BaseClass\')->__invoke($foo);

',
                'foo',
            ],
            ClassWithMixedProperties::class => [
                ClassWithMixedProperties::class,
                'unset($foo->publicProperty0, $foo->publicProperty1, $foo->publicProperty2, $foo->protectedProperty0, '
                . '$foo->protectedProperty1, $foo->protectedProperty2);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $foo, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($foo);

',
                'foo',
            ],
            ClassWithCollidingPrivateInheritedProperties::class => [
                ClassWithCollidingPrivateInheritedProperties::class,
                '\Closure::bind(function (\ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties '
                . '$instance) {
    unset($instance->property0);
}, $bar, \'ProxyManagerTestAsset\\\\ClassWithCollidingPrivateInheritedProperties\')->__invoke($bar);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithPrivateProperties $instance) {
    unset($instance->property0, $instance->property1, $instance->property2, $instance->property3, '
                . '$instance->property4, $instance->property5, $instance->property6, $instance->property7, '
                . '$instance->property8, $instance->property9);
}, $bar, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke($bar);

',
                'bar',
            ],
        ];
    }
}
