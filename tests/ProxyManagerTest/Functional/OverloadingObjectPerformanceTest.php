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

use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\OverloadingObjectGenerator;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\OverloadingObjectGenerator} produced objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Performance
 */
class OverloadingObjectPerformanceTest extends BasePerformanceTest
{
    /**
     * @outputBuffering
     * @dataProvider getTestedClasses
     *
     * @param string $className
     * @param array  $methods
     *
     * @return void
     */
    public function testProxyWithoutExtension($className, array $methods)
    {
        $proxyName   = $this->generateProxy($className, $methods);
        $iterations  = 20000;
        
        $this->startCapturing();
        for ($i = 0; $i < $iterations; $i += 1) {
            $instance = new $className();
        }
        $baseProfile = $this->endCapturing(
            'Instantiation for ' . $iterations . ' objects of type ' . $className . ': %fms / %fKb'
        );
        
        $this->startCapturing();
        for ($i = 0; $i < $iterations; $i += 1) {
            $proxy = new $proxyName();
        }
        $proxyProfile = $this->endCapturing(
            'Instantiation for ' . $iterations . ' proxies of type ' . $className . ': %fms / %fKb'
        );
        
        $this->compareProfile($baseProfile, $proxyProfile);
        
        $instance = new $className();
        $this->startCapturing();
        for ($i = 0; $i < $iterations; $i += 1) {
            $instance->publicMethod();
        }
        $baseProfile = $this->endCapturing(
            'Method call for ' . $iterations . ' objects of type ' . $className . ': %fms / %fKb'
        );
        
        $this->startCapturing();
        $proxy = new $proxyName();
        for ($i = 0; $i < $iterations; $i += 1) {
            $proxy->publicMethod();
        }

        $proxyProfile = $this->endCapturing(
            'Method call for ' . $iterations . ' proxies of type ' . $className . ': %fms / %fKb'
        );
        
        $this->compareProfile($baseProfile, $proxyProfile);
    }

    /**
     * @return array
     */
    public function getTestedClasses()
    {
        return array(
            array('ProxyManagerTestAsset\\OverloadingObjectMock', array()),
            array('ProxyManagerTestAsset\\BaseClass', array(
                'foo' => function() { return 'foo'; },
                'bar' => function($string) { return 'bar' . $string; },
            )),
        );
    }
    
    /**
     * {@inheritDoc}
     */
    protected function generateProxy($parentClassName, array $methods = array())
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new OverloadingObjectGenerator();
        $generator->setDefaultMethods($methods);
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }
}
