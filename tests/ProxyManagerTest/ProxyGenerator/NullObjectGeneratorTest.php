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

namespace ProxyManagerTest\ProxyGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\NullObjectInterface;
use ProxyManager\ProxyGenerator\NullObjectGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ReflectionClass;
use ReflectionMethod;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObjectGenerator}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\NullObjectGenerator
 * @group Coverage
 */
class NullObjectGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     *
     * @param string $className
     */
    public function testGeneratesValidCode(string $className)
    {
        $generator          = $this->getProxyGenerator();
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('AbstractProxyGeneratorTest');
        $generatedClass     = new ClassGenerator($generatedClassName);
        $originalClass      = new ReflectionClass($className);
        $generatorStrategy  = new EvaluatingGeneratorStrategy();

        $generator->generate($originalClass, $generatedClass);
        $generatorStrategy->generate($generatedClass);

        $generatedReflection = new ReflectionClass($generatedClassName);

        if ($originalClass->isInterface()) {
            $this->assertTrue($generatedReflection->implementsInterface($className));
        }

        $this->assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            $this->assertTrue($generatedReflection->implementsInterface($interface));
        }

        $proxyGenerated = $generatedClassName::staticProxyConstructor();

        foreach (Properties::fromReflectionClass($generatedReflection)->getPublicProperties() as $property) {
            $this->assertNull($proxyGenerated->$property);
        }

        foreach ($generatedReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (! ($method->getNumberOfParameters() || $method->isStatic())) {
                $this->assertNull(call_user_func([$proxyGenerated, $method->getName()]));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getProxyGenerator() : ProxyGeneratorInterface
    {
        return new NullObjectGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces() : array
    {
        return [
            NullObjectInterface::class,
        ];
    }

    /**
     * @return array
     */
    public function getTestedImplementations() : array
    {
        return [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
            [BaseInterface::class],
        ];
    }
}
