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

namespace ProxyManagerTest\Generator;

use Phar;
use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\ParameterGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\CallableTypeHintClass;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithDefaultParameters;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use stdClass;
use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Reflection\ParameterReflection;

/**
 * Tests for {@see \ProxyManager\Generator\ParameterGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Generator\ParameterGenerator
 * @group Coverage
 */
class ParameterGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testGeneratesProperTypeHint()
    {
        $generator = new ParameterGenerator('foo');

        $generator->setType('array');
        $this->assertSame('array $foo', $generator->generate());

        $generator->setType('stdClass');
        $this->assertSame('\\stdClass $foo', $generator->generate());

        $generator->setType('\\fooClass');
        $this->assertSame('\\fooClass $foo', $generator->generate());
    }

    public function testGeneratesMethodWithCallableType()
    {
        $generator = new ParameterGenerator();

        $generator->setType('callable');
        $generator->setName('foo');

        $this->assertSame('callable $foo', $generator->generate());
    }

    public function testVisitMethodWithCallable()
    {
        $parameter = new ParameterReflection(
            [CallableTypeHintClass::class, 'callableTypeHintMethod'],
            'parameter'
        );

        $generator = ParameterGenerator::fromReflection($parameter);

        $this->assertSame('callable', $generator->getType());
    }

    public function testReadsParameterDefaults()
    {
        $parameter = ParameterGenerator::fromReflection(new ParameterReflection(
            [
                ClassWithMethodWithDefaultParameters::class,
                'publicMethodWithDefaults'
            ],
            'parameter'
        ));

        /* @var $defaultValue ValueGenerator */
        $defaultValue = $parameter->getDefaultValue();

        $this->assertInstanceOf(ValueGenerator::class, $defaultValue);
        $this->assertSame(['foo'], $defaultValue->getValue());

        $this->assertStringMatchesFormat('array%a$parameter%a=%aarray(\'foo\')', $parameter->generate());
    }

    public function testReadsParameterTypeHint()
    {
        $parameter = ParameterGenerator::fromReflection(new ParameterReflection(
            [BaseClass::class, 'publicTypeHintedMethod'],
            'param'
        ));

        $this->assertSame(stdClass::class, $parameter->getType());
    }

    public function testGeneratesParameterPassedByReference()
    {
        $parameter = new ParameterGenerator('foo');

        $parameter->setPassedByReference(true);

        $this->assertStringMatchesFormat('&%A$foo', $parameter->generate());
    }

    public function testGeneratesDefaultParameterForInternalPhpClasses()
    {
        $parameter = ParameterGenerator::fromReflection(new ParameterReflection([Phar::class, 'compress'], 1));

        $this->assertSame('null', strtolower((string) $parameter->getDefaultValue()));
    }

    public function testGeneratedParametersAreProperlyEscaped()
    {
        $parameter = new ParameterGenerator();

        $parameter->setName('foo');
        $parameter->setDefaultValue('\'bar\\baz');

        $this->assertThat(
            $parameter->generate(),
            $this->logicalOr(
                $this->equalTo('$foo = \'\\\'bar\\baz\''),
                $this->equalTo('$foo = \'\\\'bar\\\\baz\'')
            )
        );
    }
}
