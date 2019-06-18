<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature;

use PHPUnit\Framework\TestCase;
use ProxyManager\Signature\SignatureGenerator;

/**
 * Tests for {@see \ProxyManager\Signature\SignatureGenerator}
 *
 * @covers \ProxyManager\Signature\SignatureGenerator
 * @group Coverage
 */
final class SignatureGeneratorTest extends TestCase
{
    private SignatureGenerator $signatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->signatureGenerator = new SignatureGenerator();
    }

    /**
     * @param array<string, array<string>> $parameters
     *
     * @dataProvider signatures
     */
    public function testGenerateSignature(array $parameters, string $expected) : void
    {
        self::assertSame($expected, $this->signatureGenerator->generateSignature($parameters));
    }

    /**
     * @param array<string, array<string>> $parameters
     *
     * @dataProvider signatureKeys
     */
    public function testGenerateSignatureKey(array $parameters, string $expected) : void
    {
        self::assertSame($expected, $this->signatureGenerator->generateSignatureKey($parameters));
    }

    /** @return array<int, array<int, array<string>|string>> */
    public static function signatures() : array
    {
        return [
            [
                [],
                'YTowOnt9',
            ],
            [
                ['foo' => 'bar'],
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30=',
            ],
            [
                ['foo' => 'bar', 'baz' => 'tab'],
                'YToyOntzOjM6ImZvbyI7czozOiJiYXIiO3M6MzoiYmF6IjtzOjM6InRhYiI7fQ==',
            ],
            [
                ['bar'],
                'YToxOntpOjA7czozOiJiYXIiO30=',
            ],
            [
                ['bar', 'baz'],
                'YToyOntpOjA7czozOiJiYXIiO2k6MTtzOjM6ImJheiI7fQ==',
            ],
        ];
    }

    /** @return string[][]|string[][][] */
    public static function signatureKeys() : array
    {
        return [
            [[], '40cd750bba9870f18aada2478b24840a'],
            [['foo' => 'bar'], '49a3696adf0fbfacc12383a2d7400d51'],
            [['foo' => 'bar', 'baz' => 'tab'], '3f3cabbf33bae82b0711205c913a8fa0'],
            [['bar'], '6fc5f617053f53f56b4734453ec86daa'],
            [['bar', 'baz'], 'b9f31192ffbb4aa958cd1c5f88540c1e'],
        ];
    }
}
