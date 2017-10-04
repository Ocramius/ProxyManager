<?php

declare(strict_types=1);

namespace ProxyManagerTest\Signature;

use PHPUnit_Framework_TestCase;
use ProxyManager\Signature\SignatureGenerator;

/**
 * Tests for {@see \ProxyManager\Signature\SignatureGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\SignatureGenerator
 * @group Coverage
 */
class SignatureGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SignatureGenerator
     */
    private $signatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->signatureGenerator = new SignatureGenerator;
    }

    /**
     * @param array  $parameters
     * @param string $expected
     *
     * @dataProvider signatures
     */
    public function testGenerateSignature(array $parameters, string $expected) : void
    {
        self::assertSame($expected, $this->signatureGenerator->generateSignature($parameters));
    }

    /**
     * @param array  $parameters
     * @param string $expected
     *
     * @dataProvider signatureKeys
     */
    public function testGenerateSignatureKey(array $parameters, string $expected) : void
    {
        self::assertSame($expected, $this->signatureGenerator->generateSignatureKey($parameters));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function signatures() : array
    {
        return [
            [
                [],
                'YTowOnt9'
            ],
            [
                ['foo' => 'bar'],
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30='
            ],
            [
                ['foo' => 'bar', 'baz' => 'tab'],
                'YToyOntzOjM6ImZvbyI7czozOiJiYXIiO3M6MzoiYmF6IjtzOjM6InRhYiI7fQ=='
            ],
            [
                ['bar'],
                'YToxOntpOjA7czozOiJiYXIiO30='
            ],
            [
                ['bar', 'baz'],
                'YToyOntpOjA7czozOiJiYXIiO2k6MTtzOjM6ImJheiI7fQ=='
            ],
        ];
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function signatureKeys() : array
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
