<?php

declare(strict_types=1);

namespace ProxyManagerTest\Inflector\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Inflector\Util\ParameterEncoder;

/**
 * Tests for {@see \ProxyManager\Inflector\Util\ParameterEncoder}
 *
 * @group Coverage
 */
final class ParameterEncoderTest extends TestCase
{
    /**
     * @param mixed[] $parameters
     *
     * @dataProvider getParameters
     * @covers \ProxyManager\Inflector\Util\ParameterEncoder::encodeParameters
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

    /** @return mixed[][] */
    public static function getParameters() : array
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
