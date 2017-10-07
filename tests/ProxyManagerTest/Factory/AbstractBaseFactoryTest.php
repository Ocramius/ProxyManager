<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory;

use PHPUnit\Framework\TestCase;
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
class AbstractBaseFactoryTest extends TestCase
{
    /**
     * Note: we mock the class in order to assert on the abstract method usage
     *
     * @var \ProxyManager\Factory\AbstractBaseFactory|\PHPUnit_Framework_MockObject_MockObject
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
        $configuration                 = $this->createMock(Configuration::class);
        $this->generator               = $this->createMock(ProxyGeneratorInterface::class);
        $this->classNameInflector      = $this->createMock(ClassNameInflectorInterface::class);
        $this->generatorStrategy       = $this->createMock(GeneratorStrategyInterface::class);
        $this->proxyAutoloader         = $this->createMock(AutoloaderInterface::class);
        $this->signatureChecker        = $this->createMock(SignatureCheckerInterface::class);
        $this->classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $configuration
            ->expects(self::any())
            ->method('getClassNameInflector')
            ->will(self::returnValue($this->classNameInflector));

        $configuration
            ->expects(self::any())
            ->method('getGeneratorStrategy')
            ->will(self::returnValue($this->generatorStrategy));

        $configuration
            ->expects(self::any())
            ->method('getProxyAutoloader')
            ->will(self::returnValue($this->proxyAutoloader));

        $configuration
            ->expects(self::any())
            ->method('getSignatureChecker')
            ->will(self::returnValue($this->signatureChecker));

        $configuration
            ->expects(self::any())
            ->method('getClassSignatureGenerator')
            ->will(self::returnValue($this->classSignatureGenerator));

        $this
            ->classNameInflector
            ->expects(self::any())
            ->method('getUserClassName')
            ->will(self::returnValue('stdClass'));

        $this->factory = $this->getMockForAbstractClass(AbstractBaseFactory::class, [$configuration]);

        $this->factory->expects(self::any())->method('getGenerator')->will(self::returnValue($this->generator));
    }

    public function testGeneratesClass() : void
    {
        $generateProxy = new ReflectionMethod($this->factory, 'generateProxy');

        $generateProxy->setAccessible(true);
        $generatedClass = UniqueIdentifierGenerator::getIdentifier('fooBar');

        $this
            ->classNameInflector
            ->expects(self::any())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will(self::returnValue($generatedClass));

        $this
            ->generatorStrategy
            ->expects(self::once())
            ->method('generate')
            ->with(self::isInstanceOf(ClassGenerator::class));
        $this
            ->proxyAutoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($generatedClass)
            ->will(self::returnCallback(function ($className) : bool {
                eval('class ' . $className . ' {}');

                return true;
            }));

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));
        $this
            ->generator
            ->expects(self::once())
            ->method('generate')
            ->with(
                self::callback(function (\ReflectionClass $reflectionClass) : bool {
                    return $reflectionClass->getName() === 'stdClass';
                }),
                self::isInstanceOf(ClassGenerator::class),
                ['some' => 'proxy', 'options' => 'here']
            );

        self::assertSame(
            $generatedClass,
            $generateProxy->invoke($this->factory, stdClass::class, ['some' => 'proxy', 'options' => 'here'])
        );
        self::assertTrue(class_exists($generatedClass, false));
        self::assertSame(
            $generatedClass,
            $generateProxy->invoke($this->factory, stdClass::class, ['some' => 'proxy', 'options' => 'here'])
        );
    }
}
