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

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicGetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializer;

    /**
     * @var MethodGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initMethod;

    /**
     * @var PublicPropertiesMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $publicProperties;

    /**
     * @var ProtectedPropertiesMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $protectedProperties;

    /**
     * @var PrivatePropertiesMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $privateProperties;

    /**
     * @var InitializationTracker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializationTracker;

    /**
     * @var string
     */
    private $expectedCode = <<<'PHP'
$this->foo && ! $this->init && $this->baz('__get', array('name' => $name));

if (isset(self::$bar[$name])) {
    return $this->$name;
}

if (isset(self::$baz[$name])) {
    if ($this->init) {
        return $this->$name;
    }

    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$baz[$name];

    if ($object instanceof $expectedType) {
        return $this->$name;
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        return $this->$name;
    }
} elseif (isset(self::$tab[$name])) {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$tab[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(function & ($instance) use ($name) {
                return $instance->$name;
            }, null, $class);

        return $accessor($this);
    }

    if ($this->init || 'ReflectionProperty' === $class) {
        $tmpClass = key(self::$tab[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(function & ($instance) use ($name) {
                return $instance->$name;
            }, null, $tmpClass);

        return $accessor($this);
    }
}

%a
PHP;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initializer      = $this->createMock(PropertyGenerator::class);
        $this->initMethod       = $this->createMock(MethodGenerator::class);
        $this->publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->protectedProperties = $this
            ->getMockBuilder(ProtectedPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->privateProperties = $this
            ->getMockBuilder(PrivatePropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->initializationTracker = $this
            ->getMockBuilder(InitializationTracker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $this->initMethod->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $this->publicProperties->expects(self::any())->method('isEmpty')->will(self::returnValue(false));
        $this->publicProperties->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $this->protectedProperties->expects(self::any())->method('getName')->will(self::returnValue('baz'));
        $this->privateProperties->expects(self::any())->method('getName')->will(self::returnValue('tab'));
        $this->initializationTracker->expects(self::any())->method('getName')->will(self::returnValue('init'));
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet
     */
    public function testBodyStructure() : void
    {
        $magicGet = new MagicGet(
            new ReflectionClass(BaseClass::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties,
            $this->initializationTracker
        );

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());

        self::assertStringMatchesFormat($this->expectedCode, $magicGet->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet
     */
    public function testBodyStructureWithOverriddenMagicGet() : void
    {
        $magicGet = new MagicGet(
            new ReflectionClass(ClassWithMagicMethods::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties,
            $this->initializationTracker
        );

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());

        self::assertStringMatchesFormat($this->expectedCode, $magicGet->getBody());
        self::assertStringMatchesFormat('%Areturn parent::__get($name);', $magicGet->getBody());
    }
}
