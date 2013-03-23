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

namespace ProxyManagerTest\Functional;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\Configuration;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 */
class LazyLoadingValueHolderPerformanceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @outputBuffering
     */
    public function testProxyInstantiationPerformance()
    {
        $this->markTestIncomplete();
    }

    /**
     * @outputBuffering
     */
    public function testProxyInitializationPerformance()
    {
        $this->markTestIncomplete();
    }

    /**
     * @outputBuffering
     */
    public function testProxiedMethodPerformance()
    {
        $this->markTestIncomplete();
    }

    /**
     * @outputBuffering
     */
    public function testProxyPropertyPerformance()
    {
        $this->markTestIncomplete();
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param  string $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\Foo' . uniqid();
        $generator          = new LazyLoadingValueHolderGenerator();
        $generatedClass     = new PhpClass($generatedClassName);
        $strategy           = new DefaultGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        eval($strategy->generate($generatedClass));

        return $generatedClassName;
    }
}
