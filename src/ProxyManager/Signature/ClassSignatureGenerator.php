<?php

declare(strict_types=1);

namespace ProxyManager\Signature;

use Zend\Code\Exception\InvalidArgumentException;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Applies a signature to a given class generator
 */
final class ClassSignatureGenerator implements ClassSignatureGeneratorInterface
{
    private SignatureGeneratorInterface $signatureGenerator;

    public function __construct(SignatureGeneratorInterface $signatureGenerator)
    {
        $this->signatureGenerator = $signatureGenerator;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function addSignature(ClassGenerator $classGenerator, array $parameters) : ClassGenerator
    {
        $classGenerator->addPropertyFromGenerator(new PropertyGenerator(
            'signature' . $this->signatureGenerator->generateSignatureKey($parameters),
            $this->signatureGenerator->generateSignature($parameters),
            PropertyGenerator::FLAG_STATIC | PropertyGenerator::FLAG_PRIVATE
        ));

        return $classGenerator;
    }
}
