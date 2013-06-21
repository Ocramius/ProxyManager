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

namespace ProxyManagerTest\Factory;

use PHPUnit_Framework_TestCase;
use ProxyManager\Configuration;
use ProxyManager\Factory\HydratorFactory;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use Zend\Code\Reflection\ClassReflection;

/**
 * Integration tests for {@see \ProxyManager\Factory\HydratorFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 */
class HydratorFactoryFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ProxyManager\Factory\HydratorFactory
     */
    protected $factory;

    /**
     * @var \ProxyManager\Configuration
     */
    protected $config;

    /**
     * @var string
     */
    protected $generatedClassName;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->config  = new Configuration();
        $this->factory = new HydratorFactory($this->config);

        $generator                = new EvaluatingGeneratorStrategy();
        $this->generatedClassName = UniqueIdentifierGenerator::getIdentifier('foo');
        $proxiedClass             = ClassGenerator::fromReflection(
            new ClassReflection('ProxyManagerTestAsset\\ClassWithMixedProperties')
        );

        $proxiedClass->setName($this->generatedClassName);
        $proxiedClass->setNamespaceName(null);
        $generator->generate($proxiedClass); // evaluating the generated class

        $this->config->setGeneratorStrategy($generator);
    }

    /**
     * @covers \ProxyManager\Factory\HydratorFactory::__construct
     * @covers \ProxyManager\Factory\HydratorFactory::createProxy
     */
    public function testWillGenerateValidProxy()
    {
        $this->assertInstanceOf(
            'Zend\\Stdlib\\Hydrator\\HydratorInterface',
            $this->factory->createProxy($this->generatedClassName)
        );
    }

    /**
     * @covers \ProxyManager\Factory\HydratorFactory::__construct
     * @covers \ProxyManager\Factory\HydratorFactory::createProxy
     */
    public function testWillCacheProxyInstancesProxy()
    {
        $this->assertSame(
            $this->factory->createProxy($this->generatedClassName),
            $this->factory->createProxy($this->generatedClassName),
            'Hydrator instances are cached'
        );
    }
}
