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
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ReflectionMethod;

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
     * @var \ProxyManager\Autoloader\AutoloaderInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $configuration                 = $this->getMock('ProxyManager\\Configuration');
        $this->generator               = $this->getMock('ProxyManager\\ProxyGenerator\\ProxyGeneratorInterface');
        $this->classNameInflector      = $this->getMock('ProxyManager\\Inflector\\ClassNameInflectorInterface');
        $this->generatorStrategy       = $this->getMock('ProxyManager\\GeneratorStrategy\\GeneratorStrategyInterface');
        $this->proxyAutoloader         = $this->getMock('ProxyManager\\Autoloader\\AutoloaderInterface');
        $this->signatureChecker        = $this->getMock('ProxyManager\\Signature\\SignatureCheckerInterface');
        $this->classSignatureGenerator = $this->getMock('ProxyManager\\Signature\\ClassSignatureGeneratorInterface');

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

        $this->factory = $this->getMockForAbstractClass(
            'ProxyManager\\Factory\\AbstractBaseFactory',
            array($configuration)
        );

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
            ->with($this->isInstanceOf('Zend\\Code\\Generator\\ClassGenerator'));
        $this
            ->proxyAutoloader
            ->expects($this->once())
            ->method('__invoke')
            ->with($generatedClass)
            ->will($this->returnCallback(function ($className) {
                eval('class ' . $className . ' {}');
            }));

        $this->signatureChecker->expects($this->atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects($this->once())->method('addSignature')->will($this->returnArgument(0));

        $this->assertSame($generatedClass, $generateProxy->invoke($this->factory, 'stdClass'));
        $this->assertTrue(class_exists($generatedClass, false));
        $this->assertSame($generatedClass, $generateProxy->invoke($this->factory, 'stdClass'));
    }
}
