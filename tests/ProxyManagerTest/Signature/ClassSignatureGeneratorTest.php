<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Signature\ClassSignatureGenerator;
use ProxyManager\Signature\SignatureGeneratorInterface;

/**
 * Tests for {@see \ProxyManager\Signature\ClassSignatureGenerator}
 *
 * @covers \ProxyManager\Signature\ClassSignatureGenerator
 * @group Coverage
 */
final class ClassSignatureGeneratorTest extends TestCase
{
    /** @var SignatureGeneratorInterface&MockObject */
    private SignatureGeneratorInterface $signatureGenerator;
    private ClassSignatureGenerator $classSignatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->signatureGenerator      = $this->createMock(SignatureGeneratorInterface::class);
        $this->classSignatureGenerator = new ClassSignatureGenerator($this->signatureGenerator);
    }

    public function testAddSignature() : void
    {
        $classGenerator = $this->createMock(ClassGenerator::class);

        $classGenerator
            ->expects(self::once())
            ->method('addPropertyFromGenerator')
            ->with(self::callback(static function (PropertyGenerator $property) : bool {
                return $property->getName() === 'signaturePropertyName'
                    && $property->isStatic()
                    && $property->getVisibility() === 'private'
                    && $property->getDefaultValue()->getValue() === 'valid-signature';
            }));

        $this
            ->signatureGenerator
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->willReturn('valid-signature');

        $this
            ->signatureGenerator
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->willReturn('PropertyName');

        $this->classSignatureGenerator->addSignature($classGenerator, ['foo' => 'bar']);
    }
}
