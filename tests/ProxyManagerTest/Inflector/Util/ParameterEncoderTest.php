<?php

declare(strict_types=1);

namespace ProxyManagerTest\Inflector\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Inflector\Util\ParameterEncoder;

/**
 * Tests for {@see \ProxyManager\Inflector\Util\ParameterEncoder}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ParameterEncoderTest extends TestCase
{
    /**
     * @dataProvider getParameters
     *
     * @covers \ProxyManager\Inflector\Util\ParameterEncoder::encodeParameters
     *
     * @param mixed[] $parameters
     */
    public function testGeneratesValidClassName(array $parameters) : void
    {
        $encoder = new ParameterEncoder();

        self::assertRegExp(
            '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+/',
            $encoder->encodeParameters($parameters),
            'Encoded string is a valid class identifier'
        );
    }

    public function getParameters() : array
    {
        return [
            [[]],
            [['foo' => 'bar']],
            [['bar' => 'baz']],
            [[null]],
            [[null, null]],
            [['bar' => null]],
            [['bar' => 12345]],
            [['foo' => 'bar', 'bar' => 'baz']],
        ];
    }
}
