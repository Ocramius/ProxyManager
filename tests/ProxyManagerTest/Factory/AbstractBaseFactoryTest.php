<?php

declare(strict_types=1);

namespace ProxyManagerTest\Factory;

use Laminas\Code\Generator\ClassGenerator;
use PHPUnit\Framework\MockObject\MockObject;
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
use ReflectionClass;
use ReflectionMethod;
use stdClass;
use function class_exists;

/**
 * Tests for {@see \ProxyManager\Factory\AbstractBaseFactory}
 *
 * @covers \ProxyManager\Factory\AbstractBaseFactory
 * @group Coverage
 */
final class AbstractBaseFactoryTest extends TestCase
{
    /**
     * Note: we mock the class in order to assert on the abstract method usage
     *
     * @var AbstractBaseFactory|MockObject
     */
    private AbstractBaseFactory $factory;

    /** @var ProxyGeneratorInterface&MockObject */
    private ProxyGeneratorInterface $generator;

    /** @var ClassNameInflectorInterface&MockObject */
    private ClassNameInflectorInterface $classNameInflector;

    /** @var GeneratorStrategyInterface&MockObject */
    private GeneratorStrategyInterface $generatorStrategy;

    /** @var AutoloaderInterface&MockObject */
    private AutoloaderInterface $proxyAutoloader;

    /** @var SignatureCheckerInterface&MockObject */
    private SignatureCheckerInterface $signatureChecker;

    /** @var ClassSignatureGeneratorInterface&MockObject */
    private ClassSignatureGeneratorInterface $classSignatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $configuration                 = $this->createMock(Configuration::class);
        $this->generator               = $this->createMock(ProxyGeneratorInterface::class);
        $this->classNameInflector      = $this->createMock(ClassNameInflectorInterface::class);
        $this->generatorStrategy       = $this->createMock(GeneratorStrategyInterface::class);
        $this->proxyAutoloader         = $this->createMock(AutoloaderInterface::class);
        $this->signatureChecker        = $this->createMock(SignatureCheckerInterface::class);
        $this->classSignatureGenerator = $this->createMock(ClassSignatureGeneratorInterface::class);

        $configuration
            ->method('getClassNameInflector')
            ->willReturn($this->classNameInflector);

        $configuration
            ->method('getGeneratorStrategy')
            ->willReturn($this->generatorStrategy);

        $configuration
            ->method('getProxyAutoloader')
            ->willReturn($this->proxyAutoloader);

        $configuration
            ->method('getSignatureChecker')
            ->willReturn($this->signatureChecker);

        $configuration
            ->method('getClassSignatureGenerator')
            ->willReturn($this->classSignatureGenerator);

        $this
            ->classNameInflector
            ->method('getUserClassName')
            ->willReturn('stdClass');

        $this->factory = $this->getMockForAbstractClass(AbstractBaseFactory::class, [$configuration]);

        $this->factory->method('getGenerator')->willReturn($this->generator);
    }

    public function testGeneratesClass() : void
    {
        $generateProxy = new ReflectionMethod($this->factory, 'generateProxy');

        $generateProxy->setAccessible(true);
        $generatedClass = UniqueIdentifierGenerator::getIdentifier('fooBar');

        $this
            ->classNameInflector
            ->method('getProxyClassName')
            ->with('stdClass')
            ->willReturn($generatedClass);

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
            ->will(self::returnCallback(static function (string $className) : bool {
                eval('class ' . $className . ' extends \\stdClass {}');

                return true;
            }));

        $this->signatureChecker->expects(self::atLeastOnce())->method('checkSignature');
        $this->classSignatureGenerator->expects(self::once())->method('addSignature')->will(self::returnArgument(0));
        $this
            ->generator
            ->expects(self::once())
            ->method('generate')
            ->with(
                self::callback(static function (ReflectionClass $reflectionClass) : bool {
                    return $reflectionClass->getName() === stdClass::class;
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
