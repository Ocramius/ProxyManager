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

use BadMethodCallException;
use PHPUnit_Framework_TestCase;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManagerTestAsset\AccessInterceptorValueHolderMock;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\CallableTypeHintClass;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerTestAsset\ClassWithFinalMethods;
use ProxyManagerTestAsset\ClassWithMethodWithDefaultParameters;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\FinalClass;
use ProxyManagerTestAsset\HydratedObject;
use ProxyManagerTestAsset\LazyLoadingMock;
use ProxyManagerTestAsset\NullObjectMock;
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
        $this->setExpectedException(InvalidProxiedClassException::class);

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(FinalClass::class));
    }

    public function testDeniesClassesWithAbstractProtectedMethods()
    {
        $this->setExpectedException(InvalidProxiedClassException::class);

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
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(BaseClass::class), false);

        $this->setExpectedException(InvalidProxiedClassException::class);

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
        $this->setExpectedException(BadMethodCallException::class);

        new CanProxyAssertion();
    }

    /**
     * @return string[][]
     */
    public function validClasses()
    {
        return [
            [AccessInterceptorValueHolderMock::class],
            [BaseClass::class],
            [BaseInterface::class],
            [CallableTypeHintClass::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithFinalMagicMethods::class],
            [ClassWithFinalMethods::class],
            [ClassWithMethodWithDefaultParameters::class],
            [ClassWithMixedProperties::class],
            [ClassWithPrivateProperties::class],
            [ClassWithProtectedProperties::class],
            [ClassWithPublicProperties::class],
            [ClassWithPublicArrayProperty::class],
            [ClassWithSelfHint::class],
            [EmptyClass::class],
            [HydratedObject::class],
            [LazyLoadingMock::class],
            [NullObjectMock::class],
        ];
    }
}
