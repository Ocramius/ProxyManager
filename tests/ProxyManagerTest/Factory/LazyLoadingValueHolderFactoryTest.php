<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\Signature\ClassSignatureGeneratorInterface;
use ProxyManager\Signature\SignatureCheckerInterface;
use ProxyManagerTest\Assert;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\LazyLoadingMock;
use function get_class;

/**
 * Tests for {@see \ProxyManager\Factory\LazyLoadingValueHolderFactory}
 *
 * @group Coverage
 */
final class LazyLoadingValueHolderFactoryTest extends TestCase
{
    /** @var ClassNameInflectorInterface&MockObject */
    private ClassNameInflectorInterface $inflector;

    /** @var SignatureCheckerInterface&MockObject */
    private SignatureCheckerInterface $signatureChecker;

    /** @var ClassSignatureGeneratorInterface&MockObject */
    private ClassSignatureGeneratorInterface $classSignatureGenerator;

    /** @var Configuration&MockObject */
    private Configuration $config;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->config                  = $this->createMock(Configuration::class);
        $this->inflector               = $this->createMock(ClassNameInflectorInterface::class);
        $this->signatureChecker        = $this->createMock(SignatureCheckerInterface::class);
        $this->classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $this
            ->config
            ->method('getClassNameInflector')
            ->willReturn($this->inflector);

        $this
            ->config
            ->method('getSignatureChecker')
            ->willReturn($this->signatureChecker);

        $this
            ->config
            ->method('getClassSignatureGenerator')
            ->willReturn($this->classSignatureGenerator);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\LazyLoadingValueHolderFactory::__construct
     */
    public static function testWithOptionalFactory() : void
    {
        self::assertInstanceOf(
            Configuration::class,
            Assert::readAttribute(new LazyLoadingValueHolderFactory(), 'configuration')
        );
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\LazyLoadingValueHolderFactory::__construct
     * @covers \ProxyManager\Factory\LazyLoadingValueHolderFactory::createProxy
     */
    public function testWillSkipAutoGeneration() : void
    {
        $className = UniqueIdentifierGenerator::getIdentifier('foo');

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with($className)
            ->willReturn(LazyLoadingMock::class);

        $factory     = new LazyLoadingValueHolderFactory($this->config);
        $initializer = static function () : bool {
            return true;
        };
        $proxy       = $factory->createProxy($className, $initializer);

        self::assertSame($initializer, $proxy->getProxyInitializer());
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\LazyLoadingValueHolderFactory::__construct
     * @covers \ProxyManager\Factory\LazyLoadingValueHolderFactory::createProxy
     * @covers \ProxyManager\Factory\LazyLoadingValueHolderFactory::getGenerator
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration() : void
    {
        $className      = UniqueIdentifierGenerator::getIdentifier('foo');
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader     = $this->createMock(AutoloaderInterface::class);

        $this->config->method('getGeneratorStrategy')->will(self::returnValue($generator));
        $this->config->method('getProxyAutoloader')->will(self::returnValue($autoloader));

        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(
                self::callback(
                    static function (ClassGenerator $targetClass) use ($proxyClassName) : bool {
                        return $targetClass->getName() === $proxyClassName;
                    }
                )
            );

        // simulate autoloading
        $autoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->willReturnCallback(static function () use ($proxyClassName) : bool {
                eval('class ' . $proxyClassName . ' extends \\ProxyManagerTestAsset\\LazyLoadingMock {}');

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with($className)
            ->willReturn($proxyClassName);

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with($className)
            ->willReturn(EmptyClass::class);

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));

        $factory     = new LazyLoadingValueHolderFactory($this->config);
        $initializer = static function () : bool {
            return true;
        };
        $proxy       = $factory->createProxy($className, $initializer);

        self::assertInstanceOf($proxyClassName, $proxy);

        self::assertSame($proxyClassName, get_class($proxy));
        self::assertSame($initializer, $proxy->getProxyInitializer());
    }
}
