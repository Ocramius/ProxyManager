<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Signature\Exception\MissingSignatureException;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\Signature\Exception\MissingSignatureException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\Exception\MissingSignatureException
 * @group Coverage
 */
class MissingSignatureExceptionTest extends TestCase
{
    public function testFromMissingSignature() : void
    {
        $exception = MissingSignatureException::fromMissingSignature(
            new ReflectionClass(__CLASS__),
            ['foo' => 'bar', 'baz' => 'tab'],
            'expected-signature'
        );

        self::assertInstanceOf(MissingSignatureException::class, $exception);

        self::assertSame(
            'No signature found for class "'
            . __CLASS__
            . '", expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
