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

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass('ProxyManagerTestAsset\\FinalClass'));
    }

    public function testDeniesClassesWithAbstractProtectedMethods()
    {
        $this->setExpectedException('ProxyManager\Exception\InvalidProxiedClassException');

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            'ProxyManagerTestAsset\\ClassWithAbstractProtectedMethod'
        ));
    }

    public function testAllowsInterfaceByDefault()
    {
        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass(
            'ProxyManagerTestAsset\\BaseInterface'
        ));

        $this->assertTrue(true); // not nice, but assertions are just fail-checks, no real code executed
    }

    public function testDeniesInterfaceIfSpecified()
    {
        $this->setExpectedException('ProxyManager\Exception\InvalidProxiedClassException');

        CanProxyAssertion::assertClassCanBeProxied(new ReflectionClass('ProxyManagerTestAsset\\BaseInterface'), false);
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
        return array(
            array('ProxyManagerTestAsset\AccessInterceptorValueHolderMock'),
            array('ProxyManagerTestAsset\BaseClass'),
            array('ProxyManagerTestAsset\BaseInterface'),
            array('ProxyManagerTestAsset\CallableTypeHintClass'),
            array('ProxyManagerTestAsset\ClassWithByRefMagicMethods'),
            array('ProxyManagerTestAsset\ClassWithFinalMagicMethods'),
            array('ProxyManagerTestAsset\ClassWithFinalMethods'),
            array('ProxyManagerTestAsset\ClassWithMethodWithDefaultParameters'),
            array('ProxyManagerTestAsset\ClassWithMixedProperties'),
            array('ProxyManagerTestAsset\ClassWithPrivateProperties'),
            array('ProxyManagerTestAsset\ClassWithProtectedProperties'),
            array('ProxyManagerTestAsset\ClassWithPublicProperties'),
            array('ProxyManagerTestAsset\ClassWithPublicArrayProperty'),
            array('ProxyManagerTestAsset\ClassWithSelfHint'),
            array('ProxyManagerTestAsset\EmptyClass'),
            array('ProxyManagerTestAsset\HydratedObject'),
            array('ProxyManagerTestAsset\LazyLoadingMock'),
            array('ProxyManagerTestAsset\NullObjectMock'),
        );
    }
}
