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

namespace ProxyManagerTest\Signature;

use PHPUnit_Framework_TestCase;
use ProxyManager\Signature\Exception\InvalidSignatureException;
use ProxyManager\Signature\Exception\MissingSignatureException;
use ProxyManager\Signature\SignatureChecker;
use ProxyManager\Signature\SignatureGeneratorInterface;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\Signature\SignatureChecker}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\SignatureChecker
 * @group Coverage
 */
class SignatureCheckerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $signatureExample = 'valid-signature';

    /**
     * @var SignatureChecker
     */
    private $signatureChecker;

    /**
     * @var \ProxyManager\Signature\SignatureGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->signatureGenerator = $this->createMock(SignatureGeneratorInterface::class);
        $this->signatureChecker   = new SignatureChecker($this->signatureGenerator);
    }

    public function testCheckSignatureWithValidKey() : void
    {
        $this
            ->signatureGenerator
            ->expects(self::atLeastOnce())
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('Example'));
        $this
            ->signatureGenerator
            ->expects(self::atLeastOnce())
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('valid-signature'));

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }

    public function testCheckSignatureWithInvalidKey() : void
    {
        $this
            ->signatureGenerator
            ->expects(self::any())
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('InvalidKey'));
        $this
            ->signatureGenerator
            ->expects(self::any())
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('valid-signature'));

        $this->expectException(MissingSignatureException::class);

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }

    public function testCheckSignatureWithInvalidValue() : void
    {
        $this
            ->signatureGenerator
            ->expects(self::any())
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('Example'));
        $this
            ->signatureGenerator
            ->expects(self::any())
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('invalid-signature'));

        $this->expectException(InvalidSignatureException::class);

        $this->signatureChecker->checkSignature(new ReflectionClass($this), ['foo' => 'bar']);
    }
}
