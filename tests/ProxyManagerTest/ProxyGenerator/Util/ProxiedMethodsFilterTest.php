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
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithAbstractMagicMethods;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\HydratedObject;
use ProxyManagerTestAsset\LazyLoadingMock;
use ReflectionClass;
use ReflectionMethod;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter
 * @group Coverage
 */
class ProxiedMethodsFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider expectedMethods
     *
     * @param ReflectionClass $reflectionClass
     * @param array|null      $excludes
     * @param array           $expectedMethods
     */
    public function testFiltering(ReflectionClass $reflectionClass, $excludes, array $expectedMethods)
    {
        if (null === $excludes) {
            $filtered = ProxiedMethodsFilter::getProxiedMethods($reflectionClass);
        } else {
            $filtered = ProxiedMethodsFilter::getProxiedMethods($reflectionClass, $excludes);
        }

        foreach ($filtered as $method) {
            $this->assertInstanceOf(ReflectionMethod::class, $method);
        }

        $keys = array_map(
            function (ReflectionMethod $method) {
                return $method->getName();
            },
            $filtered
        );

        sort($keys);
        sort($expectedMethods);

        $this->assertSame($keys, $expectedMethods);
    }

    /**
     * @dataProvider expectedAbstractPublicMethods
     *
     * @param ReflectionClass $reflectionClass
     * @param array|null      $excludes
     * @param array           $expectedMethods
     */
    public function testFilteringOfAbstractPublic(ReflectionClass $reflectionClass, $excludes, array $expectedMethods)
    {
        if (null === $excludes) {
            $filtered = ProxiedMethodsFilter::getAbstractProxiedMethods($reflectionClass);
        } else {
            $filtered = ProxiedMethodsFilter::getAbstractProxiedMethods($reflectionClass, $excludes);
        }

        foreach ($filtered as $method) {
            $this->assertInstanceOf(ReflectionMethod::class, $method);
        }

        $keys = array_map(
            function (ReflectionMethod $method) {
                return $method->getName();
            },
            $filtered
        );

        sort($keys);
        sort($expectedMethods);

        $this->assertSame($keys, $expectedMethods);
    }

    /**
     * Data provider
     *
     * @return array[][]
     */
    public function expectedMethods()
    {
        return [
            [
                new ReflectionClass(BaseClass::class),
                null,
                [
                    'privatePropertyGetter',
                    'protectedPropertyGetter',
                    'publicArrayHintedMethod',
                    'publicByReferenceMethod',
                    'publicByReferenceParameterMethod',
                    'publicMethod',
                    'publicPropertyGetter',
                    'publicTypeHintedMethod',
                ],
            ],
            [
                new ReflectionClass(EmptyClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(LazyLoadingMock::class),
                null,
                [],
            ],
            [
                new ReflectionClass(LazyLoadingMock::class),
                [],
                [],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                ['doFoo'],
                ['__get'],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                ['Dofoo'],
                ['__get'],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                [],
                ['doFoo', '__get'],
            ],
            [
                new ReflectionClass(ClassWithAbstractProtectedMethod::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                null,
                ['publicAbstractMethod'],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                ['publicAbstractMethod'],
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                [],
                [
                    '__clone',
                    '__get',
                    '__isset',
                    '__set',
                    '__sleep',
                    '__unset',
                    '__wakeup',
                ],
            ],
        ];
    }

    /**
     * Data provider
     *
     * @return array[][]
     */
    public function expectedAbstractPublicMethods()
    {
        return [
            [
                new ReflectionClass(BaseClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(EmptyClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractProtectedMethod::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                null,
                ['publicAbstractMethod'],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                ['publicAbstractMethod'],
                [],
            ],
            [
                new ReflectionClass(ClassWithMagicMethods::class),
                [],
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                [],
                [
                    '__clone',
                    '__get',
                    '__isset',
                    '__set',
                    '__sleep',
                    '__unset',
                    '__wakeup',
                ],
            ],
        ];
    }
}
