<?php

declare(strict_types=1);

namespace ProxyManagerTest\Inflector\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Inflector\Util\ParameterHasher;

/**
 * Tests for {@see \ProxyManager\Inflector\Util\ParameterHasher}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ParameterHasherTest extends TestCase
{
    /**
     * @dataProvider getParameters
     *
     * @covers \ProxyManager\Inflector\Util\ParameterHasher::hashParameters
     *
     * @param mixed[] $parameters
     * @param string  $expectedHash
     */
    public function testGeneratesValidClassName(array $parameters, string $expectedHash) : void
    {
        $encoder = new ParameterHasher();

        self::assertSame($expectedHash, $encoder->hashParameters($parameters));
    }

    public function getParameters() : array
    {
        return [
            [[], '40cd750bba9870f18aada2478b24840a'],
            [['foo' => 'bar'], '49a3696adf0fbfacc12383a2d7400d51'],
            [['bar' => 'baz'], '6ed41c8a63c1571554ecaeb998198757'],
            [[null], '38017a839aaeb8ff1a658fce9af6edd3'],
            [[null, null], '12051f9a58288e5328ad748881cc4e00'],
            [['bar' => null], '0dbb112e1c4e6e4126232de2daa2d660'],
            [['bar' => 12345], 'eb6291ea4973741bf9b6571f49b4ffd2'],
            [['foo' => 'bar', 'bar' => 'baz'], '4447ff857f244d24c31bd84d7a855eda'],
        ];
    }
}
