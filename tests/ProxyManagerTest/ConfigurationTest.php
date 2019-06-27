<?php

declare(strict_types=1);

namespace ProxyManagerTest;

use PHPUnit\Framework\TestCase;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\Signature\ClassSignatureGeneratorInterface;
use ProxyManager\Signature\SignatureCheckerInterface;
use ProxyManager\Signature\SignatureGeneratorInterface;

/**
 * Tests for {@see \ProxyManager\Configuration}
 *
 * @group Coverage
 */
final class ConfigurationTest extends TestCase
{
    private Configuration $configuration;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->configuration = new Configuration();
    }

    /**
     * @covers \ProxyManager\Configuration::getProxiesNamespace
     * @covers \ProxyManager\Configuration::setProxiesNamespace
     */
    public function testGetSetProxiesNamespace() : void
    {
        self::assertSame(
            'ProxyManagerGeneratedProxy',
            $this->configuration->getProxiesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setProxiesNamespace('foo');
        self::assertSame('foo', $this->configuration->getProxiesNamespace());
    }

    /**
     * @covers \ProxyManager\Configuration::getClassNameInflector
     * @covers \ProxyManager\Configuration::setClassNameInflector
     */
    public function testSetGetClassNameInflector() : void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(ClassNameInflectorInterface::class, $this->configuration->getClassNameInflector());

        $inflector = $this->createMock(ClassNameInflectorInterface::class);

        $this->configuration->setClassNameInflector($inflector);
        self::assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    /**
     * @covers \ProxyManager\Configuration::getGeneratorStrategy
     */
    public function testDefaultGeneratorStrategyNeedToBeAInstanceOfEvaluatingGeneratorStrategy() : void
    {
        self::assertInstanceOf(EvaluatingGeneratorStrategy::class, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \ProxyManager\Configuration::getGeneratorStrategy
     * @covers \ProxyManager\Configuration::setGeneratorStrategy
     */
    public function testSetGetGeneratorStrategy() : void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(GeneratorStrategyInterface::class, $this->configuration->getGeneratorStrategy());

        $strategy = $this->createMock(GeneratorStrategyInterface::class);

        $this->configuration->setGeneratorStrategy($strategy);
        self::assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \ProxyManager\Configuration::getProxiesTargetDir
     * @covers \ProxyManager\Configuration::setProxiesTargetDir
     */
    public function testSetGetProxiesTargetDir() : void
    {
        self::assertDirectoryExists($this->configuration->getProxiesTargetDir());

        $this->configuration->setProxiesTargetDir(__DIR__);
        self::assertSame(__DIR__, $this->configuration->getProxiesTargetDir());
    }

    /**
     * @covers \ProxyManager\Configuration::getProxyAutoloader
     * @covers \ProxyManager\Configuration::setProxyAutoloader
     */
    public function testSetGetProxyAutoloader() : void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(AutoloaderInterface::class, $this->configuration->getProxyAutoloader());

        $autoloader = $this->createMock(AutoloaderInterface::class);

        $this->configuration->setProxyAutoloader($autoloader);
        self::assertSame($autoloader, $this->configuration->getProxyAutoloader());
    }

    /**
     * @covers \ProxyManager\Configuration::getSignatureGenerator
     * @covers \ProxyManager\Configuration::setSignatureGenerator
     */
    public function testSetGetSignatureGenerator() : void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(SignatureCheckerInterface::class, $this->configuration->getSignatureChecker());

        $signatureGenerator = $this->createMock(SignatureGeneratorInterface::class);

        $this->configuration->setSignatureGenerator($signatureGenerator);
        self::assertSame($signatureGenerator, $this->configuration->getSignatureGenerator());
    }

    /**
     * @covers \ProxyManager\Configuration::getSignatureChecker
     * @covers \ProxyManager\Configuration::setSignatureChecker
     */
    public function testSetGetSignatureChecker() : void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(SignatureCheckerInterface::class, $this->configuration->getSignatureChecker());

        $signatureChecker = $this->createMock(SignatureCheckerInterface::class);

        $this->configuration->setSignatureChecker($signatureChecker);
        self::assertSame($signatureChecker, $this->configuration->getSignatureChecker());
    }

    /**
     * @covers \ProxyManager\Configuration::getClassSignatureGenerator
     * @covers \ProxyManager\Configuration::setClassSignatureGenerator
     */
    public function testSetGetClassSignatureGenerator() : void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(
            ClassSignatureGeneratorInterface::class,
            $this->configuration->getClassSignatureGenerator()
        );
        $classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $this->configuration->setClassSignatureGenerator($classSignatureGenerator);
        self::assertSame($classSignatureGenerator, $this->configuration->getClassSignatureGenerator());
    }
}
