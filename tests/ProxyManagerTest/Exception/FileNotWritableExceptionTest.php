<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\FileNotWritableException;
use Webimpress\SafeWriter\Exception\ExceptionInterface as FileWriterException;

/**
 * Tests for {@see \ProxyManager\Exception\FileNotWritableException}
 *
 * @covers \ProxyManager\Exception\FileNotWritableException
 * @group Coverage
 */
final class FileNotWritableExceptionTest extends TestCase
{
    public function testFromPrevious() : void
    {
        $previousExceptionMock = $this->getMockBuilder(FileWriterException::class);
        $previousExceptionMock->enableOriginalConstructor();
        $previousExceptionMock->setConstructorArgs(['Previous exception message']);
        $previousException = $previousExceptionMock->getMock();

        $exception = FileNotWritableException::fromPrevious($previousException);

        self::assertSame('Previous exception message', $exception->getMessage());
        self::assertSame($previousException, $exception->getPrevious());
    }
}
