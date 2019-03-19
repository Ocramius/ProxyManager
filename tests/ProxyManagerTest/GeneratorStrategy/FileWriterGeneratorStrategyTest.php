<?php

declare(strict_types=1);

namespace ProxyManagerTest\GeneratorStrategy;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\FileLocator\FileLocatorInterface;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use const PATH_SEPARATOR;
use const SCANDIR_SORT_ASCENDING;
use function class_exists;
use function clearstatcache;
use function decoct;
use function fileperms;
use function ini_set;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function strpos;
use function sys_get_temp_dir;
use function umask;
use function uniqid;

/**
 * Tests for {@see \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy}
 *
 * @group Coverage
 * @covers \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy
 * @runTestsInSeparateProcesses
 *
 * Note: this test generates temporary files that are not deleted
 */
final class FileWriterGeneratorStrategyTest extends TestCase
{
    /** @var string */
    private $tempDir;

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/' . uniqid('FileWriterGeneratorStrategyTest', true);

        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }

        ini_set('open_basedir', __DIR__ . '/../../..' . PATH_SEPARATOR . $this->tempDir);
    }

    public function testGenerate() : void
    {
        /** @var FileLocatorInterface|MockObject $locator */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $this->tempDir . '/' . uniqid('FileWriterGeneratorStrategyTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will(self::returnValue($tmpFile));

        $body = $generator->generate(new ClassGenerator($fqcn));

        self::assertGreaterThan(0, strpos($body, $className));
        self::assertFalse(class_exists($fqcn, false));
        self::assertFileExists($tmpFile);
        self::assertFileIsReadable($tmpFile);

        // a user note on php.net recommended calling this as we have just called chmod on a file.
        clearstatcache();

        // Calculate the permission that should have been set.
        // The operators below are bit-wise "AND" (&) and "NOT" (~), read more at: http://php.net/manual/en/language.operators.bitwise.php
        $perm = 0666 & ~umask();

        self::assertSame($perm, fileperms($tmpFile) & 0777, 'File permission was not correct: ' . decoct($perm));

        /* @noinspection PhpIncludeInspection */
        require $tmpFile;

        self::assertTrue(class_exists($fqcn, false));
    }

    public function testGenerateWillFailIfTmpFileCannotBeWrittenToDisk() : void
    {
        $tmpDirPath = $this->tempDir . '/' . uniqid('nonWritable', true);
        mkdir($tmpDirPath, 0555, true);

        /** @var FileLocatorInterface|MockObject $locator */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $tmpDirPath . '/' . uniqid('FileWriterGeneratorStrategyFailedFileWriteTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->method('getProxyFileName')
            ->with($fqcn)
            ->willReturn($tmpFile);

        $this->expectException(FileNotWritableException::class);
        $generator->generate(new ClassGenerator($fqcn));
    }

    public function testGenerateWillFailIfTmpFileCannotBeMovedToFinalDestination() : void
    {
        /** @var FileLocatorInterface|MockObject $locator */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $this->tempDir . '/' . uniqid('FileWriterGeneratorStrategyFailedFileMoveTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->method('getProxyFileName')
            ->with($fqcn)
            ->willReturn($tmpFile);

        mkdir($tmpFile);

        $this->expectException(FileNotWritableException::class);
        $generator->generate(new ClassGenerator($fqcn));
    }

    public function testWhenFailingAllTemporaryFilesAreRemoved() : void
    {
        $tmpDirPath = $this->tempDir . '/' . uniqid('noTempFilesLeftBehind', true);

        mkdir($tmpDirPath);

        /** @var FileLocatorInterface|MockObject $locator */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $tmpDirPath . '/' . uniqid('FileWriterGeneratorStrategyFailedFileMoveTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->method('getProxyFileName')
            ->with($fqcn)
            ->willReturn($tmpFile);

        mkdir($tmpFile);

        try {
            $generator->generate(new ClassGenerator($fqcn));

            self::fail('An exception was supposed to be thrown');
        } catch (FileNotWritableException $exception) {
            rmdir($tmpFile);

            self::assertEquals(['.', '..'], scandir($tmpDirPath, SCANDIR_SORT_ASCENDING));
        }
    }
}
