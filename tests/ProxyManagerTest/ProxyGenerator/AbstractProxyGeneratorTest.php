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
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ReflectionClass;

/**
 * Base test for proxy generators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class AbstractProxyGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Verifies that generated code is valid and implements expected interfaces
     */
    public function testGeneratesValidCode()
    {
        $generator          = $this->getProxyGenerator();
        $generatedClassName = 'AbstractProxyGeneratorTest_' . uniqid();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $originalClass      = new ReflectionClass('ProxyManagerTestAsset\\BaseClass');
        $generatorStrategy  = new EvaluatingGeneratorStrategy();

        $generator->generate($originalClass, $generatedClass);
        $generatorStrategy->generate($generatedClass);

        $generatedReflection = new ReflectionClass($generatedClassName);

        $this->assertSame($generatedClassName, $generatedReflection->getName());
        $this->assertSame($originalClass->getName(), $generatedReflection->getParentClass()->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            $this->assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    /**
     * Retrieve a new generator instance
     *
     * @return \ProxyManager\ProxyGenerator\ProxyGeneratorInterface
     */
    abstract protected function getProxyGenerator();

    /**
     * Retrieve interfaces that should be implemented by the generated code
     *
     * @return string[]
     */
    abstract protected function getExpectedImplementedInterfaces();
}
