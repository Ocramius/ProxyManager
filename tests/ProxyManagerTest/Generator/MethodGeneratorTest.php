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

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassScalarTypeHinted;
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

        $this->assertSame(true, $methodGenerator->returnsReference());
        $this->assertStringMatchesFormat(
            '%a/**%adocBlock%a*/%aprotected function & methodName($foo)%a{%a/* body */%a}',
            $methodGenerator->generate()
        );
    }

    public function testGenerateMethodWithVariadicParameter()
    {
        $methodGenerator = new MethodGenerator();

        $methodGenerator->setReturnsReference(true);
        $methodGenerator->setName('methodName');

        $parameter = new ParameterGenerator('foo');
        $parameter->setVariadic(true);

        $methodGenerator->setParameter($parameter);

        $this->assertStringMatchesFormat(
            '%apublic function & methodName(...$foo)%a{%a%a}',
            $methodGenerator->generate()
        );
    }

    /**
     * Verify that building from reflection works
     */
    public function testGenerateFromReflection()
    {
        $method = MethodGenerator::fromReflection(new MethodReflection(__CLASS__, __FUNCTION__));

        $this->assertSame(__FUNCTION__, $method->getName());
        $this->assertSame(MethodGenerator::VISIBILITY_PUBLIC, $method->getVisibility());
        $this->assertFalse($method->isStatic());
        $this->assertSame('Verify that building from reflection works', $method->getDocBlock()->getShortDescription());

        $method = MethodGenerator::fromReflection(new MethodReflection(BaseClass::class, 'protectedMethod'));

        $this->assertSame(MethodGenerator::VISIBILITY_PROTECTED, $method->getVisibility());

        $method = MethodGenerator::fromReflection(new MethodReflection(BaseClass::class, 'privateMethod'));

        $this->assertSame(MethodGenerator::VISIBILITY_PRIVATE, $method->getVisibility());
    }

    public function testGeneratedParametersFromReflection()
    {
        $method = MethodGenerator::fromReflection(new MethodReflection(
            BaseClass::class,
            'publicTypeHintedMethod'
        ));

        $this->assertSame('publicTypeHintedMethod', $method->getName());

        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);

        $param = $parameters['param'];

        $this->assertSame(stdClass::class, $param->getType());
    }

    /**
     * @param string $methodName
     * @param        $type
     *
     * @dataProvider scalarTypeHintedMethods
     */
    public function testGenerateMethodWithScalarTypeHinting($methodName, $type)
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped(sprintf(
                'Can\'t run tests about scalar type hinting for %s',
                phpversion()
            ));
        }

        $method = MethodGenerator::fromReflection(new MethodReflection(
            ClassScalarTypeHinted::class,
            $methodName
        ));

        $this->assertSame($methodName, $method->getName());

        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);

        $param = $parameters['param'];

        $this->assertSame($type, $param->getType());
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

}
