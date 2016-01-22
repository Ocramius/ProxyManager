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

namespace ProxyManagerTest\Factory;

use PHPUnit_Framework_TestCase;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\Signature\ClassSignatureGeneratorInterface;
use ProxyManager\Signature\SignatureCheckerInterface;
use ReflectionMethod;
use stdClass;
use Zend\Code\Generator\ClassGenerator;

/**
 * Tests for {@see \ProxyManager\Factory\AbstractBaseFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Factory\AbstractBaseFactory
 * @group Coverage
 */
class AbstractBaseFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ProxyManager\Factory\AbstractBaseFactory
     */
    private $factory;

    /**
     * @var \ProxyManager\ProxyGenerator\ProxyGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    /**
     * @var \ProxyManager\Inflector\ClassNameInflectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classNameInflector;

    /**
     * @var \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorStrategy;

    /**
     * @var AutoloaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $proxyAutoloader;

    /**
     * @var \ProxyManager\Signature\SignatureCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signatureChecker;

    /**
     * @var \ProxyManager\Signature\ClassSignatureGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classSignatureGenerator;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $configuration                 = $this->getMock(Configuration::class);
        $this->generator               = $this->getMock(ProxyGeneratorInterface::class);
        $this->classNameInflector      = $this->getMock(ClassNameInflectorInterface::class);
        $this->generatorStrategy       = $this->getMock(GeneratorStrategyInterface::class);
        $this->proxyAutoloader         = $this->getMock(AutoloaderInterface::class);
        $this->signatureChecker        = $this->getMock(SignatureCheckerInterface::class);
        $this->classSignatureGenerator = $this->getMock(ClassSignatureGeneratorInterface::class);

        $configuration
            ->expects($this->any())
            ->method('getClassNameInflector')
            ->will($this->returnValue($this->classNameInflector));

        $configuration
            ->expects($this->any())
            ->method('getGeneratorStrategy')
            ->will($this->returnValue($this->generatorStrategy));

        $configuration
            ->expects($this->any())
            ->method('getProxyAutoloader')
            ->will($this->returnValue($this->proxyAutoloader));

        $configuration
            ->expects($this->any())
            ->method('getSignatureChecker')
            ->will($this->returnValue($this->signatureChecker));

        $configuration
            ->expects($this->any())
            ->method('getClassSignatureGenerator')
            ->will($this->returnValue($this->classSignatureGenerator));

        $this
            ->classNameInflector
            ->expects($this->any())
            ->method('getUserClassName')
            ->will($this->returnValue('stdClass'));

        $this->factory = $this->getMockForAbstractClass(AbstractBaseFactory::class, [$configuration]);

        $this->factory->expects($this->any())->method('getGenerator')->will($this->returnValue($this->generator));
    }

    public function testGeneratesClass()
    {
        $generateProxy = new ReflectionMethod($this->factory, 'generateProxy');

        $generateProxy->setAccessible(true);
        $generatedClass = UniqueIdentifierGenerator::getIdentifier('fooBar');

        $this
            ->classNameInflector
            ->expects($this->any())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will($this->returnValue($generatedClass));

        $this
            ->generatorStrategy
            ->expects($this->once())
            ->method('generate')
            ->with($this->isInstanceOf(ClassGenerator::class));
        $this
            ->proxyAutoloader
            ->expects($this->once())
            ->method('__invoke')
            ->with($generatedClass)
            ->will($this->returnCallback(function ($className) : bool {
                eval('class ' . $className . ' {}');

                return true;
            }));

        $this->signatureChecker->expects($this->atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects($this->once())->method('addSignature')->will($this->returnArgument(0));
        $this
            ->generator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->callback(function (\ReflectionClass $reflectionClass) : bool {
                    return $reflectionClass->getName() === 'stdClass';
                }),
                $this->isInstanceOf(ClassGenerator::class),
                ['some' => 'proxy', 'options' => 'here']
            );

        $this->assertSame(
            $generatedClass,
            $generateProxy->invoke($this->factory, stdClass::class, ['some' => 'proxy', 'options' => 'here'])
        );
        $this->assertTrue(class_exists($generatedClass, false));
        $this->assertSame(
            $generatedClass,
            $generateProxy->invoke($this->factory, stdClass::class, ['some' => 'proxy', 'options' => 'here'])
        );
    }
}
