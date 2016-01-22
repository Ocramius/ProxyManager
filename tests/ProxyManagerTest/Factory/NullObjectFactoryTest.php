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
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\Factory\NullObjectFactory;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\Signature\ClassSignatureGeneratorInterface;
use ProxyManager\Signature\SignatureCheckerInterface;
use ProxyManagerTestAsset\NullObjectMock;
use stdClass;

/**
 * Tests for {@see \ProxyManager\Factory\NullObjectFactory}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class NullObjectFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $inflector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $signatureChecker;

    /**
     * @var ClassSignatureGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classSignatureGenerator;

    /**
     * @var Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->config                  = $this->getMock(Configuration::class);
        $this->inflector               = $this->getMock(ClassNameInflectorInterface::class);
        $this->signatureChecker        = $this->getMock(SignatureCheckerInterface::class);
        $this->classSignatureGenerator = $this->getMock(ClassSignatureGeneratorInterface::class);

        $this
            ->config
            ->expects($this->any())
            ->method('getClassNameInflector')
            ->will($this->returnValue($this->inflector));

        $this
            ->config
            ->expects($this->any())
            ->method('getSignatureChecker')
            ->will($this->returnValue($this->signatureChecker));

        $this
            ->config
            ->expects($this->any())
            ->method('getClassSignatureGenerator')
            ->will($this->returnValue($this->classSignatureGenerator));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\NullObjectFactory::__construct
     * @covers \ProxyManager\Factory\NullObjectFactory::createProxy
     * @covers \ProxyManager\Factory\NullObjectFactory::getGenerator
     */
    public function testWillSkipAutoGeneration()
    {
        $instance = new stdClass();

        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will($this->returnValue(NullObjectMock::class));

        $factory    = new NullObjectFactory($this->config);
        /* @var $proxy NullObjectMock */
        $proxy      = $factory->createProxy($instance);

        $this->assertInstanceOf(NullObjectMock::class, $proxy);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\NullObjectFactory::__construct
     * @covers \ProxyManager\Factory\NullObjectFactory::createProxy
     * @covers \ProxyManager\Factory\NullObjectFactory::getGenerator
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration()
    {
        $instance       = new stdClass();
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->getMock('ProxyManager\GeneratorStrategy\\GeneratorStrategyInterface');
        $autoloader     = $this->getMock(AutoloaderInterface::class);

        $this->config->expects($this->any())->method('getGeneratorStrategy')->will($this->returnValue($generator));
        $this->config->expects($this->any())->method('getProxyAutoloader')->will($this->returnValue($autoloader));

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->callback(
                    function (ClassGenerator $targetClass) use ($proxyClassName) {
                        return $targetClass->getName() === $proxyClassName;
                    }
                )
            );

        // simulate autoloading
        $autoloader
            ->expects($this->once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->willReturnCallback(function () use ($proxyClassName) {
                eval('class ' . $proxyClassName . ' extends \\ProxyManagerTestAsset\\NullObjectMock {}');

                return true;
            });

        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will($this->returnValue($proxyClassName));

        $this
            ->inflector
            ->expects($this->once())
            ->method('getUserClassName')
            ->with('stdClass')
            ->will($this->returnValue(NullObjectMock::class));

        $this->signatureChecker->expects($this->atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects($this->once())->method('addSignature')->will($this->returnArgument(0));

        $factory    = new NullObjectFactory($this->config);
        /* @var $proxy NullObjectMock */
        $proxy      = $factory->createProxy($instance);

        $this->assertInstanceOf($proxyClassName, $proxy);
    }
}
