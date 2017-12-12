<?php

declare(strict_types=1);

namespace ProxyManagerTest\GeneratorStrategy;

use PHPUnit\Framework\TestCase;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\FileLocator\FileLocatorInterface;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Tests for {@see \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 * @covers \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy
 *
 * Note: this test generates temporary files that are not deleted
 */
class FileWriterGeneratorStrategyTest extends TestCase
{
    public function testGenerate() : void
    {
        /* @var $locator FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = sys_get_temp_dir() . '/' . uniqid('FileWriterGeneratorStrategyTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects(self::any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will(self::returnValue($tmpFile));

        $body = $generator->generate(new ClassGenerator($fqcn));

        self::assertGreaterThan(0, strpos($body, $className));
        self::assertFalse(class_exists($fqcn, false));
        self::assertFileExists($tmpFile);

        /* @noinspection PhpIncludeInspection */
        require $tmpFile;

        self::assertTrue(class_exists($fqcn, false));
    }

    public function testGenerateWillFailIfTmpFileCannotBeWrittenToDisk() : void
    {
        $tmpDirPath = sys_get_temp_dir() . '/' . uniqid('nonWritable', true);

        mkdir($tmpDirPath, 0555, true);

        /* @var $locator FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $tmpDirPath . '/' . uniqid('FileWriterGeneratorStrategyFailedFileWriteTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects(self::any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will(self::returnValue($tmpFile));

        $this->expectException(FileNotWritableException::class);
        $generator->generate(new ClassGenerator($fqcn));
    }

    public function testGenerateWillFailIfTmpFileCannotBeMovedToFinalDestination() : void
    {
        /* @var $locator \ProxyManager\FileLocator\FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = sys_get_temp_dir() . '/' . uniqid('FileWriterGeneratorStrategyFailedFileMoveTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects(self::any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will(self::returnValue($tmpFile));

        mkdir($tmpFile);

        $this->expectException(FileNotWritableException::class);
        $generator->generate(new ClassGenerator($fqcn));
    }

    public function testWhenFailingAllTemporaryFilesAreRemoved() : void
    {
        $tmpDirPath = sys_get_temp_dir() . '/' . uniqid('noTempFilesLeftBehind', true);

        mkdir($tmpDirPath);

        /* @var $locator FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $tmpDirPath . '/' . uniqid('FileWriterGeneratorStrategyFailedFileMoveTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects(self::any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will(self::returnValue($tmpFile));

        mkdir($tmpFile);

        try {
            $generator->generate(new ClassGenerator($fqcn));

            self::fail('An exception was supposed to be thrown');
        } catch (FileNotWritableException $exception) {
            rmdir($tmpFile);

            self::assertEquals(['.', '..'], scandir($tmpDirPath));
        }
    }

    public function testClassNotExistsException(): void
    {
        /* @var $locator FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;
        $proxyClassName = $fqcn;
        $proxyFolder = sys_get_temp_dir() . '/';
        $tmpFile   = $proxyFolder . $namespace . $className . '.php';
        $configuration = $this->createMock(Configuration::class);
        $proxyAutoloader = $this->createMock(AutoloaderInterface::class);

        $configuration
            ->expects(self::any())
            ->method('getProxyAutoloader')
            ->will(self::returnValue($proxyAutoloader));

        $configuration
            ->expects(self::any())
            ->method('getProxiesTargetDir')
            ->willReturn($proxyFolder);

        $proxyAutoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->will(self::returnCallback(function ($className) : bool {
                // We does not include file here.
                return true;
            }));

        spl_autoload_register(function ($name) use ($fqcn) {
            if ($name === $fqcn) {
                throw new \Exception(sprintf("Cannot load class %s", $name));
            }
        });

        $locator
            ->expects(self::any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will(self::returnValue($tmpFile));

        $body = $generator->generate(new ClassGenerator($fqcn));


        self::assertFileExists($tmpFile);
        self::expectException(\Exception::class);
        self::expectExceptionMessage(sprintf("Cannot load class %s", $fqcn));
        $generator->classExists($proxyClassName, $configuration);
        rmdir($tmpFile);
    }

    public function testClassNotExistsLoaded(): void
    {
        /* @var $locator FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;
        $proxyClassName = $fqcn;
        $proxyFolder = sys_get_temp_dir() . '/';
        $tmpFile   = $proxyFolder . $namespace . $className . '.php';
        $configuration = $this->createMock(Configuration::class);
        $proxyAutoloader = $this->createMock(AutoloaderInterface::class);

        $configuration
            ->expects(self::any())
            ->method('getProxyAutoloader')
            ->will(self::returnValue($proxyAutoloader));

        $configuration
            ->expects(self::any())
            ->method('getProxiesTargetDir')
            ->willReturn($proxyFolder);

        $proxyAutoloader
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->will(self::returnCallback(function ($className) use ($fqcn, $tmpFile) : bool {
                if ($fqcn === $className) {
                    /* @noinspection PhpIncludeInspection */
                    /* @noinspection UsingInclusionOnceReturnValueInspection */
                    require_once $tmpFile;
                }

                return true;
            }));

        spl_autoload_register(function ($name) use ($fqcn) {
            if ($name === $fqcn) {
                throw new \Exception(sprintf("Cannot load class %s", $name));
            }
        });

        $locator
            ->expects(self::any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will(self::returnValue($tmpFile));

        $body = $generator->generate(new ClassGenerator($fqcn));


        self::assertFileExists($tmpFile);
        self::assertTrue($generator->classExists($proxyClassName, $configuration));
        unlink($tmpFile);
    }
}
