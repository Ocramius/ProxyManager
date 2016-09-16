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

namespace ProxyManagerTest\Generator;

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\MethodGenerator;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\ReturnTypeHintedClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use Zend\Code\Generator\ParameterGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ScalarTypeHintedClass;
use stdClass;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\Generator\MethodGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Generator\MethodGenerator
 * @group Coverage
 */
class MethodGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testGenerateSimpleMethod()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setReturnsReference(true);
        $methodGenerator->setName('methodName');
        $methodGenerator->setVisibility('protected');
        $methodGenerator->setBody('/* body */');
        $methodGenerator->setDocBlock('docBlock');
        $methodGenerator->setParameter(new ParameterGenerator('foo'));

        self::assertStringMatchesFormat(
            '%a/**%adocBlock%a*/%aprotected function & methodName($foo)%a{%a/* body */%a}',
            $methodGenerator->generate()
        );
    }

    /**
     * Verify that building from reflection works
     */
    public function testGenerateFromReflection()
    {
        $method = MethodGenerator::fromReflection(new MethodReflection(__CLASS__, __FUNCTION__));

        self::assertSame(__FUNCTION__, $method->getName());
        self::assertSame(MethodGenerator::VISIBILITY_PUBLIC, $method->getVisibility());
        self::assertFalse($method->isStatic());
        self::assertSame('Verify that building from reflection works', $method->getDocBlock()->getShortDescription());

        $method = MethodGenerator::fromReflection(new MethodReflection(BaseClass::class, 'protectedMethod'));

        self::assertSame(MethodGenerator::VISIBILITY_PROTECTED, $method->getVisibility());

        $method = MethodGenerator::fromReflection(new MethodReflection(BaseClass::class, 'privateMethod'));

        self::assertSame(MethodGenerator::VISIBILITY_PRIVATE, $method->getVisibility());
    }

    public function testGeneratedParametersFromReflection()
    {
        $method = MethodGenerator::fromReflection(new MethodReflection(
            BaseClass::class,
            'publicTypeHintedMethod'
        ));

        self::assertSame('publicTypeHintedMethod', $method->getName());

        $parameters = $method->getParameters();

        self::assertCount(1, $parameters);

        $param = $parameters['param'];

        self::assertSame(stdClass::class, $param->getType());
    }

    /**
     * @param string $methodName
     * @param string $type
     *
     * @dataProvider scalarTypeHintedMethods
     */
    public function testGenerateMethodWithScalarTypeHinting(string $methodName, string $type)
    {
        $method = MethodGenerator::fromReflection(new MethodReflection(
            ScalarTypeHintedClass::class,
            $methodName
        ));

        self::assertSame($methodName, $method->getName());

        $parameters = $method->getParameters();

        self::assertCount(1, $parameters);

        $param = $parameters['param'];

        self::assertSame($type, $param->getType());
    }

    public function scalarTypeHintedMethods()
    {
        return [
            ['acceptString', 'string'],
            ['acceptInteger', 'int'],
            ['acceptBoolean', 'bool'],
            ['acceptFloat', 'float'],
        ];
    }

    public function testGenerateMethodWithVoidReturnTypeHinting()
    {
        $method = MethodGenerator::fromReflection(new MethodReflection(
            VoidMethodTypeHintedInterface::class,
            'returnVoid'
        ));

        self::assertSame('returnVoid', $method->getName());
        self::assertStringMatchesFormat('%a : void%a', $method->generate());
    }

    /**
     * @dataProvider returnTypeHintsProvider
     *
     * @param string $methodName
     * @param string $expectedType
     *
     * @return void
     */
    public function testReturnTypeHintGeneration(string $methodName, string $expectedType) : void
    {
        $method = MethodGenerator::fromReflection(new MethodReflection(
            ReturnTypeHintedClass::class,
            $methodName
        ));

        self::assertSame($methodName, $method->getName());
        self::assertStringMatchesFormat('%a : ' . $expectedType . '%a', $method->generate());
    }

    /**
     * @return string[][]
     */
    public function returnTypeHintsProvider() : array
    {
        return [
            ['returnString', 'string'],
            ['returnInteger', 'int'],
            ['returnBool', 'bool'],
            ['returnArray', 'array'],
            ['returnCallable', 'callable'],
            ['returnSelf', '\\' . ReturnTypeHintedClass::class],
            ['returnParent', '\\' . EmptyClass::class],
            ['returnVoid', 'void'],
            ['returnIterable', 'iterable'],
            ['returnSameClass', '\\' . ReturnTypeHintedClass::class],
            ['returnOtherClass', '\\' . EmptyClass::class],
        ];
    }
}
