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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Constructor;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Constructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{
    private $prefixInterceptors;
    private $suffixInterceptors;
    public function setUp()
    {
        $this->prefixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $this->suffixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $this->prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $this->suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Constructor::__construct
     */
    public function testSignature()
    {
        $constructor = new Constructor(
            new ReflectionClass('ProxyManagerTestAsset\\ClassWithProtectedProperties'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );
        $this->assertSame('__construct', $constructor->getName());

        $parameters = $constructor->getParameters();

        $this->assertCount(3, $parameters);

        $this->assertSame(
            'ProxyManagerTestAsset\\ClassWithProtectedProperties',
            $parameters['localizedObject']->getType()
        );
        $this->assertSame('array', $parameters['prefixInterceptors']->getType());
        $this->assertSame('array', $parameters['suffixInterceptors']->getType());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructure()
    {
        $constructor = new Constructor(
            new ReflectionClass('ProxyManagerTestAsset\\ClassWithPublicProperties'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $this->assertSame(
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
            $constructor->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructureWithProtectedProperties()
    {
        $constructor = new Constructor(
            new ReflectionClass('ProxyManagerTestAsset\\ClassWithProtectedProperties'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $this->assertSame(
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
            $constructor->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructureWithPrivateProperties()
    {
        if (! method_exists('Closure', 'bind')) {
            $this->setExpectedException('ProxyManager\Exception\UnsupportedProxiedClassException');
        }

        $constructor = new Constructor(
            new ReflectionClass('ProxyManagerTestAsset\\ClassWithPrivateProperties'),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $this->assertSame(
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
            $constructor->getBody()
        );
    }
}
