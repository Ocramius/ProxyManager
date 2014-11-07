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

namespace ProxyManagerTest\ProxyGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\NullObjectGenerator;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObjectGenerator}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\NullObjectGenerator
 * @group Coverage
 */
class NullObjectGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     */
    public function testGeneratesValidCode($className)
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

        $proxyGenerated = new $generatedClassName();

        foreach ($generatedReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $this->assertNull($proxyGenerated->$property);
        }

        /** @var \ReflectionMethod $method */
        foreach ($generatedReflection->getMethods(ReflectionProperty::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() == 0) {
                $this->assertNull(call_user_func(array($proxyGenerated, $method->getName())));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getProxyGenerator()
    {
        return new NullObjectGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces()
    {
        return array(
            'ProxyManager\\Proxy\\NullObjectInterface',
        );
    }

    /**
     * @return array
     */
    public function getTestedImplementations()
    {
        return array(
            array('ProxyManagerTestAsset\\BaseClass'),
            array('ProxyManagerTestAsset\\ClassWithMagicMethods'),
            array('ProxyManagerTestAsset\\ClassWithByRefMagicMethods'),
            array('ProxyManagerTestAsset\\ClassWithMixedProperties'),
            array('ProxyManagerTestAsset\\BaseInterface'),
        );
    }
}
