<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\FileNotWritableException;

/**
 * Tests for {@see \ProxyManager\Exception\FileNotWritableException}
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

    public function testFromNotWritableDirectory() : void
    {
        $exception = FileNotWritableException::fromNotWritableDirectory('/tmp/a');

        self::assertInstanceOf(FileNotWritableException::class, $exception);
        self::assertSame(
            'Could not create temp file in directory "/tmp/a" '
            . 'either the directory does not exist, or it is not writable',
            $exception->getMessage()
        );
    }
}
