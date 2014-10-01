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
     */
    public function testFiltering(ReflectionClass $reflectionClass, $excludes, array $expectedMethods)
    {
        if (is_array($excludes)) {
            $filtered = ProxiedMethodsFilter::getProxiedMethods($reflectionClass, $excludes);
        } else {
            $filtered = ProxiedMethodsFilter::getProxiedMethods($reflectionClass);
        }

        foreach ($filtered as $method) {
            $this->assertInstanceOf('ReflectionMethod', $method);
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
     * @return array[][]
     */
    public function expectedMethods()
    {
        return array(
            array(
                new ReflectionClass('ProxyManagerTestAsset\\BaseClass'),
                null,
                array(
                    'publicArrayHintedMethod',
                    'publicByReferenceMethod',
                    'publicByReferenceParameterMethod',
                    'publicMethod',
                    'publicTypeHintedMethod',
                ),
            ),
            array(
                new ReflectionClass('ProxyManagerTestAsset\\EmptyClass'),
                null,
                array(),
            ),
            array(
                new ReflectionClass('ProxyManagerTestAsset\\LazyLoadingMock'),
                null,
                array(),
            ),
            array(
                new ReflectionClass('ProxyManagerTestAsset\\LazyLoadingMock'),
                array(),
                array(),
            ),
            array(
                new ReflectionClass('ProxyManagerTestAsset\\HydratedObject'),
                array('doFoo'),
                array('__get'),
            ),
            array(
                new ReflectionClass('ProxyManagerTestAsset\\HydratedObject'),
                array('Dofoo'),
                array('__get'),
            ),
            array(
                new ReflectionClass('ProxyManagerTestAsset\\HydratedObject'),
                array(),
                array('doFoo', '__get'),
            ),
        );
    }
}
