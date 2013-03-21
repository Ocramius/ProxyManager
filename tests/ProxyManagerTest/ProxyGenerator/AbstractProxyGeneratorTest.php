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

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Base test for proxy generators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class AbstractProxyGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testExtendsOriginalClass()
    {
        $generator       = $this->getProxyGenerator();
        $generatedClass  = new PhpClass('AbstractProxyGeneratorTest_' . uniqid());
        $originalClass   = new ReflectionClass('ProxyManagerTestAsset\\EmptyClass');

        $generator->generate($originalClass, $generatedClass);

        $this->assertSame($originalClass->getName(), $generatedClass->getParentClassName());
    }

    public function testGeneratesValidCode()
    {
        $generator          = $this->getProxyGenerator();
        $generatedClassName = 'AbstractProxyGeneratorTest_' . uniqid();
        $generatedClass     = new PhpClass($generatedClassName);
        $originalClass      = new ReflectionClass('ProxyManagerTestAsset\\BaseClass');

        $generator->generate($originalClass, $generatedClass);

        $generatorStrategy = new DefaultGeneratorStrategy();
        $classBody         = $generatorStrategy->generate($generatedClass);

        eval($classBody);

        $generatedReflectionClass = new ReflectionClass($generatedClassName);

        $this->assertSame($generatedClassName, $generatedReflectionClass->getName());
        $this->assertSame($originalClass->getName(), $generatedReflectionClass->getParentClass()->getName());
    }

    /**
     * @return \CG\Proxy\GeneratorInterface
     */
    abstract protected function getProxyGenerator();
}