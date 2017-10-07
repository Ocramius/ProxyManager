<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\FileNotWritableException;

/**
 * Tests for {@see \ProxyManager\Exception\FileNotWritableException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Exception\FileNotWritableException
 * @group Coverage
 */
class FileNotWritableExceptionTest extends TestCase
{
    public function testFromInvalidMoveOperation() : void
    {
        $exception = FileNotWritableException::fromInvalidMoveOperation('/tmp/a', '/tmp/b');

        self::assertInstanceOf(FileNotWritableException::class, $exception);
        self::assertSame(
            'Could not move file "/tmp/a" to location "/tmp/b": either the source file is not readable,'
            . ' or the destination is not writable',
            $exception->getMessage()
        );
    }

    public function testFromNotWritableLocationWithNonFilePath() : void
    {
        $exception = FileNotWritableException::fromNonWritableLocation(__DIR__);

        self::assertInstanceOf(FileNotWritableException::class, $exception);
        self::assertSame(
            'Could not write to path "' . __DIR__ . '": exists and is not a file',
            $exception->getMessage()
        );
    }

    public function testFromNotWritableLocationWithNonWritablePath() : void
    {
        $path = sys_get_temp_dir() . '/' . uniqid('FileNotWritableExceptionTestNonWritable', true);

        mkdir($path, 0555);

        $exception = FileNotWritableException::fromNonWritableLocation($path);

        self::assertInstanceOf(FileNotWritableException::class, $exception);
        self::assertSame(
            'Could not write to path "' . $path . '": exists and is not a file, is not writable',
            $exception->getMessage()
        );
    }

    public function testFromNonExistingPath() : void
    {
        $path = sys_get_temp_dir() . '/' . uniqid('FileNotWritableExceptionTestNonWritable', true);

        $exception = FileNotWritableException::fromNonWritableLocation($path . '/foo');

        self::assertInstanceOf(FileNotWritableException::class, $exception);
        self::assertSame(
            'Could not write to path "' . $path . '/foo": path does not exist',
            $exception->getMessage()
        );
    }
}
