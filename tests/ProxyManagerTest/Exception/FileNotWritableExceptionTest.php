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
        $previous = $this->getMockBuilder(FileWriterException::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(['Previous exception message'])
            ->getMock();

        $exception = FileNotWritableException::fromPrevious($previous);

        self::assertSame('Previous exception message', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }
}
