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

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator
 * @group Coverage
 */
class PublicScopeSimulatorTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleGet() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $backtrace = debug_backtrace(false);
    trigger_error(
        sprintf(
            'Undefined property: %s::%s in %s on line %s',
            get_parent_class($this),
            $foo,
            $backtrace[0]['file']
        ),
        \E_USER_NOTICE
    );
    return $targetObject->$foo;
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function & () use ($targetObject, $name) {
    return $targetObject->$foo;
};
$backtrace = debug_backtrace(true);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = & $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'foo',
                null,
                null,
                'bar'
            )
        );
    }

    public function testSimpleSet() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    return $targetObject->$foo = $baz;
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function & () use ($targetObject, $name, $value) {
    return $targetObject->$foo = $baz;
};
$backtrace = debug_backtrace(true);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = & $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'foo',
                'baz',
                null,
                'bar'
            )
        );
    }

    public function testSimpleIsset() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    return isset($targetObject->$foo);
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function () use ($targetObject, $name) {
    return isset($targetObject->$foo);
};
$backtrace = debug_backtrace(true);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_ISSET,
                'foo',
                null,
                null,
                'bar'
            )
        );
    }

    public function testSimpleUnset() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    unset($targetObject->$foo);
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function () use ($targetObject, $name) {
    unset($targetObject->$foo);
};
$backtrace = debug_backtrace(true);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_UNSET,
                'foo',
                null,
                null,
                'bar'
            )
        );
    }

    public function testSetRequiresValueParameterName() : void
    {
        $this->expectException(InvalidArgumentException::class);

        PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_SET,
            'foo',
            null,
            null,
            'bar'
        );
    }

    public function testDelegatesToValueHolderWhenAvailable() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this->valueHolder;

    return $targetObject->$foo = $baz;
    return;
}

$targetObject = $this->valueHolder;
$accessor = function & () use ($targetObject, $name, $value) {
    return $targetObject->$foo = $baz;
};
$backtrace = debug_backtrace(true);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = & $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'foo',
                'baz',
                new PropertyGenerator('valueHolder'),
                'bar'
            )
        );
    }

    public function testSetRequiresValidOperation() : void
    {
        $this->expectException(InvalidArgumentException::class);

        PublicScopeSimulator::getPublicAccessSimulationCode('invalid', 'foo');
    }

    public function testWillReturnDirectlyWithNoReturnParam() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $backtrace = debug_backtrace(false);
    trigger_error(
        sprintf(
            'Undefined property: %s::%s in %s on line %s',
            get_parent_class($this),
            $foo,
            $backtrace[0]['file']
        ),
        \E_USER_NOTICE
    );
    return $targetObject->$foo;
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function & () use ($targetObject, $name) {
    return $targetObject->$foo;
};
$backtrace = debug_backtrace(true);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$returnValue = & $accessor();

return $returnValue;
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'foo'
            )
        );
    }
}
