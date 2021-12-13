<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\Signature\ClassSignatureGeneratorInterface;
use ProxyManager\Signature\SignatureCheckerInterface;
use ProxyManagerTest\Assert;
use ProxyManagerTestAsset\AccessInterceptorValueHolderMock;
use ProxyManagerTestAsset\LazyLoadingMock;
use stdClass;

/**
 * @covers \ProxyManager\Factory\AbstractBaseFactory
 * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory
 * @group Coverage
 */
final class AccessInterceptorScopeLocalizerFactoryTest extends TestCase
{
    /** @var ClassNameInflectorInterface&MockObject */
    private ClassNameInflectorInterface $inflector;

    /** @var SignatureCheckerInterface&MockObject */
    private SignatureCheckerInterface $signatureChecker;

    /** @var ClassSignatureGeneratorInterface&MockObject */
    private ClassSignatureGeneratorInterface $classSignatureGenerator;

    /** @var Configuration&MockObject */
    private Configuration $config;

    protected function setUp(): void
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
     * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::__construct
     */
    public static function testWithOptionalFactory(): void
    {
        self::assertInstanceOf(
            Configuration::class,
            Assert::readAttribute(
                new AccessInterceptorValueHolderFactory(),
                'configuration'
            )
        );
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::__construct
     * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::createProxy
     * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::getGenerator
     */
    public function testWillSkipAutoGeneration(): void
    {
        $instance = new stdClass();

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->willReturn(AccessInterceptorValueHolderMock::class);

        $factory            = new AccessInterceptorScopeLocalizerFactory($this->config);
        $prefixInterceptors = [
            'methodName' => static function (): void {
                self::fail('Not supposed to be called');
            },
        ];
        $suffixInterceptors = [
            'methodName' => static function (): void {
                self::fail('Not supposed to be called');
            },
        ];

        $proxy = $factory->createProxy($instance, $prefixInterceptors, $suffixInterceptors);

        self::assertSame($instance, $proxy->instance);
        self::assertSame($prefixInterceptors, $proxy->prefixInterceptors);
        self::assertSame($suffixInterceptors, $proxy->suffixInterceptors);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::__construct
     * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::createProxy
     * @covers \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::getGenerator
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration(): void
    {
        $instance       = new stdClass();
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->createMock(GeneratorStrategyInterface::class);
        $autoloader     = $this->createMock(AutoloaderInterface::class);

        $this->config->method('getGeneratorStrategy')->willReturn($generator);
        $this->config->method('getProxyAutoloader')->willReturn($autoloader);

        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(
                self::callback(
                    static function (ClassGenerator $targetClass) use ($proxyClassName): bool {
                        return $targetClass->getName() === $proxyClassName;
                    }
                )
            );

        // simulate autoloading
        $autoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->willReturnCallback(static function () use ($proxyClassName): bool {
                eval(
                    'class ' . $proxyClassName
                    . ' extends \\ProxyManagerTestAsset\\AccessInterceptorValueHolderMock {}'
                );

                return true;
            });

        $this
            ->inflector
            ->expects(self::once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->willReturn($proxyClassName);

        $this
            ->inflector
            ->expects(self::once())
            ->method('getUserClassName')
            ->with('stdClass')
            ->willReturn(LazyLoadingMock::class);

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));

        $factory            = new AccessInterceptorScopeLocalizerFactory($this->config);
        $prefixInterceptors = [
            'methodName' => static function (): void {
                self::fail('Not supposed to be called');
            },
        ];
        $suffixInterceptors = [
            'methodName' => static function (): void {
                self::fail('Not supposed to be called');
            },
        ];
        $proxy              = $factory->createProxy($instance, $prefixInterceptors, $suffixInterceptors);

        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf($proxyClassName, $proxy);

        self::assertSame($instance, $proxy->instance);
        self::assertSame($prefixInterceptors, $proxy->prefixInterceptors);
        self::assertSame($suffixInterceptors, $proxy->suffixInterceptors);
    }
}
