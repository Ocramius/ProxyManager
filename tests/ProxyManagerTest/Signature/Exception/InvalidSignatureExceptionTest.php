<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\Signature\Exception\InvalidSignatureException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\Exception\InvalidSignatureException
 * @group Coverage
 */
class InvalidSignatureExceptionTest extends TestCase
{
    public function testFromInvalidSignature() : void
    {
        $exception = InvalidSignatureException::fromInvalidSignature(
            new ReflectionClass(__CLASS__),
            ['foo' => 'bar', 'baz' => 'tab'],
            'blah',
            'expected-signature'
        );

        self::assertInstanceOf(InvalidSignatureException::class, $exception);

        self::assertSame(
            'Found signature "blah" for class "'
            . __CLASS__
            . '" does not correspond to expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
