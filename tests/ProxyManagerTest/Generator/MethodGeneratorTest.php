<?php

declare(strict_types=1);

namespace ProxyManagerTest\Generator;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\MethodGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\ReturnTypeHintedClass;
use ProxyManagerTestAsset\ScalarTypeHintedClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use stdClass;
use Zend\Code\Generator\ParameterGenerator;
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
class MethodGeneratorTest extends TestCase
{
    public function testGeneratedMethodsAreAllConcrete() : void
    {
        $methodGenerator = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
            ClassWithAbstractPublicMethod::class,
            'publicAbstractMethod'
        ));

        self::assertFalse($methodGenerator->isInterface());
    }

    public function testGenerateSimpleMethod() : void
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
    public function testGenerateFromReflection() : void
    {
        $method = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
            __CLASS__,
            __FUNCTION__
        ));

        self::assertSame(__FUNCTION__, $method->getName());
        self::assertSame(MethodGenerator::VISIBILITY_PUBLIC, $method->getVisibility());
        self::assertFalse($method->isStatic());
        self::assertNull($method->getDocBlock(), 'The docblock is ignored');
        self::assertNull($method->getBody(), 'The body is ignored');
        self::assertNull($method->getSourceContent(), 'The source content ignored');
        self::assertTrue($method->isSourceDirty(), 'Dirty because the source cannot just be re-used when generating');

        $method = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
            BaseClass::class,
            'protectedMethod'
        ));

        self::assertSame(MethodGenerator::VISIBILITY_PROTECTED, $method->getVisibility());

        $method = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
            BaseClass::class,
            'privateMethod'
        ));

        self::assertSame(MethodGenerator::VISIBILITY_PRIVATE, $method->getVisibility());
    }

    public function testGeneratedParametersFromReflection() : void
    {
        $method = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
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
    public function testGenerateMethodWithScalarTypeHinting(string $methodName, string $type) : void
    {
        $method = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
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

    public function testGenerateMethodWithVoidReturnTypeHinting() : void
    {
        $method = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
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
     */
    public function testReturnTypeHintGeneration(string $methodName, string $expectedType) : void
    {
        $method = MethodGenerator::fromReflectionWithoutBodyAndDocBlock(new MethodReflection(
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
