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

namespace ProxyManagerTest\ProxyGenerator\Assertion;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\FinalClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion
 * @group Coverage
 */
class CanProxyAssertionTest extends PHPUnit_Framework_TestCase
{
    public function testDeniesFinalClasses()
    {
        $this->setExpectedException('ProxyManager\Exception\InvalidProxiedClassException');

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(FinalClass::class));
    }

    public function testDeniesClassesWithAbstractProtectedMethods()
    {
        $this->setExpectedException('ProxyManager\Exception\InvalidProxiedClassException');

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            ClassWithAbstractProtectedMethod::class
        ));
    }

    public function testAllowsInterfaceByDefault()
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            BaseInterface::class
        ));

        $this->assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDeniesInterfaceIfSpecified()
    {
        $this->setExpectedException('ProxyManager\Exception\InvalidProxiedClassException');

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseInterface::class), false);
    }

    /**
     * @param string $className
     *
     * @dataProvider validClasses
     */
    public function testAllowedClass($className)
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass($className));

        $this->assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDisallowsConstructor()
    {
        $this->setExpectedException('BadMethodCallException');

        new CanProxyAssertion();
    }

    /**
     * @return string[][]
     */
    public function validClasses()
    {
        return [
            ['ProxyManagerTestAsset\AccessInterceptorValueHolderMock'],
            ['ProxyManagerTestAsset\BaseClass'],
            ['ProxyManagerTestAsset\BaseInterface'],
            ['ProxyManagerTestAsset\CallableTypeHintClass'],
            ['ProxyManagerTestAsset\ClassWithByRefMagicMethods'],
            ['ProxyManagerTestAsset\ClassWithFinalMagicMethods'],
            ['ProxyManagerTestAsset\ClassWithFinalMethods'],
            ['ProxyManagerTestAsset\ClassWithMethodWithDefaultParameters'],
            ['ProxyManagerTestAsset\ClassWithMixedProperties'],
            ['ProxyManagerTestAsset\ClassWithPrivateProperties'],
            ['ProxyManagerTestAsset\ClassWithProtectedProperties'],
            ['ProxyManagerTestAsset\ClassWithPublicProperties'],
            ['ProxyManagerTestAsset\ClassWithPublicArrayProperty'],
            ['ProxyManagerTestAsset\ClassWithSelfHint'],
            ['ProxyManagerTestAsset\EmptyClass'],
            ['ProxyManagerTestAsset\HydratedObject'],
            ['ProxyManagerTestAsset\LazyLoadingMock'],
            ['ProxyManagerTestAsset\NullObjectMock'],
        ];
    }
}
