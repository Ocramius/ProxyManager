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

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class CallInitializerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer
     */
    public function testBodyStructure()
    {
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer           = $this->getMock(PropertyGenerator::class);
        /* @var $initializationTracker PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializationTracker = $this->getMock(PropertyGenerator::class);

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('init'));
        $initializationTracker->expects($this->any())->method('getName')->will($this->returnValue('track'));

        $callInitializer = new CallInitializer(
            $initializer,
            $initializationTracker,
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        );

        $expectedCode = 'if ($this->track || ! $this->init) {
    return;
}

$this->track = true;

$this->publicProperty0 = \'publicProperty0\';
$this->publicProperty1 = \'publicProperty1\';
$this->publicProperty2 = \'publicProperty2\';
$this->protectedProperty0 = \'protectedProperty0\';
$this->protectedProperty1 = \'protectedProperty1\';
$this->protectedProperty2 = \'protectedProperty2\';
static $cacheProxyManagerTestAsset_ClassWithMixedProperties;

$cacheProxyManagerTestAsset_ClassWithMixedProperties ?: $cacheProxyManagerTestAsset_ClassWithMixedProperties = '
        . '\Closure::bind(function ($instance) {
    $instance->privateProperty0 = \'privateProperty0\';
    $instance->privateProperty1 = \'privateProperty1\';
    $instance->privateProperty2 = \'privateProperty2\';
}, null, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\');

$cacheProxyManagerTestAsset_ClassWithMixedProperties($this);




$properties = [
    \'publicProperty0\' => & $this->publicProperty0,
    \'publicProperty1\' => & $this->publicProperty1,
    \'publicProperty2\' => & $this->publicProperty2,
    \'\' . "\0" . \'*\' . "\0" . \'protectedProperty0\' => & $this->protectedProperty0,
    \'\' . "\0" . \'*\' . "\0" . \'protectedProperty1\' => & $this->protectedProperty1,
    \'\' . "\0" . \'*\' . "\0" . \'protectedProperty2\' => & $this->protectedProperty2,
];

static $cacheFetchProxyManagerTestAsset_ClassWithMixedProperties;

$cacheFetchProxyManagerTestAsset_ClassWithMixedProperties ?: $cacheFetchProxyManagerTestAsset_ClassWithMixedProperties '
            . '= \Closure::bind(function ($instance, array & $properties) {
    $properties[\'\' . "\0" . \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\' . "\0" . \'privateProperty0\'] = '
            . '& $instance->privateProperty0;
    $properties[\'\' . "\0" . \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\' . "\0" . \'privateProperty1\'] = '
            . '& $instance->privateProperty1;
    $properties[\'\' . "\0" . \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\' . "\0" . \'privateProperty2\'] = '
            . '& $instance->privateProperty2;
}, $this, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\');

$cacheFetchProxyManagerTestAsset_ClassWithMixedProperties($this, $properties);

$result = $this->init->__invoke($this, $methodName, $parameters, $this->init, $properties);
$this->track = false;

return $result;';

        $this->assertSame(
            $expectedCode,
            $callInitializer->getBody()
        );
    }
}
