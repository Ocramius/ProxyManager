<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature;

use PHPUnit\Framework\TestCase;
use ProxyManager\Signature\ClassSignatureGenerator;
use ProxyManager\Signature\SignatureGeneratorInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\Signature\ClassSignatureGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\ClassSignatureGenerator
 * @group Coverage
 */
class ClassSignatureGeneratorTest extends TestCase
{
    /**
     * @var SignatureGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signatureGenerator;

    /**
     * @var ClassSignatureGenerator
     */
    private $classSignatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->signatureGenerator      = $this->createMock(SignatureGeneratorInterface::class);
        $this->classSignatureGenerator = new ClassSignatureGenerator($this->signatureGenerator);
    }

    public function testAddSignature() : void
    {
        /* @var $classGenerator \PHPUnit_Framework_MockObject_MockObject|ClassGenerator */
        $classGenerator = $this->createMock(ClassGenerator::class);

        $classGenerator
            ->expects(self::once())
            ->method('addPropertyFromGenerator')
            ->with(self::callback(function (PropertyGenerator $property) : bool {
                return $property->getName() === 'signaturePropertyName'
                    && $property->isStatic()
                    && $property->getVisibility() === 'private'
                    && $property->getDefaultValue()->getValue() === 'valid-signature';
            }));

        $this
            ->signatureGenerator
            ->expects(self::any())
            ->method('generateSignature')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('valid-signature'));

        $this
            ->signatureGenerator
            ->expects(self::any())
            ->method('generateSignatureKey')
            ->with(['foo' => 'bar'])
            ->will(self::returnValue('PropertyName'));

        $this->classSignatureGenerator->addSignature($classGenerator, ['foo' => 'bar']);
    }
}
