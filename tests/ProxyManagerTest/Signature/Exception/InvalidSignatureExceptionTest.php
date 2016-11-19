<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace ProxyManagerTest\Signature\Exception;

use PHPUnit_Framework_TestCase;
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
class InvalidSignatureExceptionTest extends PHPUnit_Framework_TestCase
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
