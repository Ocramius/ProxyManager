<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature;

use PHPUnit\Framework\TestCase;
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
class SignatureCheckerTest extends TestCase
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
