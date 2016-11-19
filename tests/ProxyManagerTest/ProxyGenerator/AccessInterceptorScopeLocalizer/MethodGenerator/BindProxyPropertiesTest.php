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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties
 * @group Coverage
 */
class BindProxyPropertiesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $prefixInterceptors;

    /**
     * @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $suffixInterceptors;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $this->prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $this->suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));
    }

    public function testSignature() : void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithProtectedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );
        self::assertSame('bindProxyProperties', $method->getName());
        self::assertSame('private', $method->getVisibility());
        self::assertFalse($method->isStatic());

        $parameters = $method->getParameters();

        self::assertCount(3, $parameters);

        self::assertSame(
            ClassWithProtectedProperties::class,
            $parameters['localizedObject']->getType()
        );
        self::assertSame('array', $parameters['prefixInterceptors']->getType());
        self::assertSame('array', $parameters['suffixInterceptors']->getType());
    }

    public function testBodyStructure() : void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithMixedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $expectedCode = <<<'PHP'
$this->publicProperty0 = & $localizedObject->publicProperty0;

$this->publicProperty1 = & $localizedObject->publicProperty1;

$this->publicProperty2 = & $localizedObject->publicProperty2;

$this->protectedProperty0 = & $localizedObject->protectedProperty0;

$this->protectedProperty1 = & $localizedObject->protectedProperty1;

$this->protectedProperty2 = & $localizedObject->protectedProperty2;

\Closure::bind(function () use ($localizedObject) {
    $this->privateProperty0 = & $localizedObject->privateProperty0;
}, $this, 'ProxyManagerTestAsset\\ClassWithMixedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateProperty1 = & $localizedObject->privateProperty1;
}, $this, 'ProxyManagerTestAsset\\ClassWithMixedProperties')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->privateProperty2 = & $localizedObject->privateProperty2;
}, $this, 'ProxyManagerTestAsset\\ClassWithMixedProperties')->__invoke();

$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;
PHP;

        self::assertSame($expectedCode, $method->getBody());
    }

    public function testBodyStructureWithProtectedProperties() : void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithProtectedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertSame(
            '$this->property0 = & $localizedObject->property0;

$this->property1 = & $localizedObject->property1;

$this->property2 = & $localizedObject->property2;

$this->property3 = & $localizedObject->property3;

$this->property4 = & $localizedObject->property4;

$this->property5 = & $localizedObject->property5;

$this->property6 = & $localizedObject->property6;

$this->property7 = & $localizedObject->property7;

$this->property8 = & $localizedObject->property8;

$this->property9 = & $localizedObject->property9;

$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;',
            $method->getBody()
        );
    }

    public function testBodyStructureWithPrivateProperties() : void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithPrivateProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        self::assertSame(
            '\Closure::bind(function () use ($localizedObject) {
    $this->property0 = & $localizedObject->property0;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property1 = & $localizedObject->property1;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property2 = & $localizedObject->property2;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property3 = & $localizedObject->property3;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property4 = & $localizedObject->property4;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property5 = & $localizedObject->property5;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property6 = & $localizedObject->property6;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property7 = & $localizedObject->property7;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property8 = & $localizedObject->property8;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

\Closure::bind(function () use ($localizedObject) {
    $this->property9 = & $localizedObject->property9;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke();

$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;',
            $method->getBody()
        );
    }
}
