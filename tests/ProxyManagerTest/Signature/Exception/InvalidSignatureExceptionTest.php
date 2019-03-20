<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\Signature\Exception\InvalidSignatureException}
 *
 * @covers \ProxyManager\Signature\Exception\InvalidSignatureException
 * @group Coverage
 */
final class InvalidSignatureExceptionTest extends TestCase
{
    public function testFromInvalidSignature() : void
    {
        $exception = InvalidSignatureException::fromInvalidSignature(
            new ReflectionClass(self::class),
            ['foo' => 'bar', 'baz' => 'tab'],
            'blah',
            'expected-signature'
        );

        self::assertSame(
            'Found signature "blah" for class "'
            . self::class
            . '" does not correspond to expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
