<?php

declare(strict_types=1);

namespace ProxyManager\Signature;

/**
 * Generator for signatures to be used to check the validity of generated code
 */
interface SignatureGeneratorInterface
{
    /**
     * Generates a signature to be used to verify generated code validity
     *
     * @param array<string, mixed> $parameters
     */
    public function generateSignature(array $parameters): string;

    /**
     * Generates a signature key to be looked up when verifying generated code validity
     *
     * @param array<string, mixed> $parameters
     */
    public function generateSignatureKey(array $parameters): string;
}
