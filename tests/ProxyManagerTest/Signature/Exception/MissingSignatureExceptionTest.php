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

namespace ProxyManagerTest\Signature\Exception;

use PHPUnit_Framework_TestCase;
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
class MissingSignatureExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testFromMissingSignature()
    {
        $exception = MissingSignatureException::fromMissingSignature(
            new ReflectionClass(__CLASS__),
            array('foo' => 'bar', 'baz' => 'tab'),
            'expected-signature'
        );

        $this->assertInstanceOf(
            'ProxyManager\Signature\Exception\MissingSignatureException',
            $exception
        );

        $this->assertSame(
            'No signature found for class "'
            . __CLASS__
            . '", expected signature "expected-signature" for 2 parameters',
            $exception->getMessage()
        );
    }
}
