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
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator
 */
class LazyLoadingValueHolderGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * {@inheritDoc}
     */
    protected function getProxyGenerator()
    {
        return new LazyLoadingValueHolderGenerator();
    }

    public function testGeneratedCodeImplementation()
    {
        $generator = new LazyLoadingValueHolderGenerator();

        $generatedClassName = 'LazyLoadingValueHolderGeneratorTest_' . uniqid();
        $generatedClass     = new PhpClass($generatedClassName);
        $originalClass      = new ReflectionClass('stdClass');
        $generatorStrategy  = new DefaultGeneratorStrategy();

        $generator->generate($originalClass, $generatedClass);

        $classBody = $generatorStrategy->generate($generatedClass);

        eval($classBody);

        $generatedReflection = new ReflectionClass($generatedClassName);

        $this->assertTrue($generatedReflection->implementsInterface('ProxyManager\\Proxy\\LazyLoadingInterface'));
        $this->assertTrue($generatedReflection->implementsInterface('ProxyManager\\Proxy\\ValueHolderInterface'));
    }
}
