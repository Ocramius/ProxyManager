<?php

declare(strict_types=1);

namespace ProxyManagerTest\Generator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ProxyManagerTestAsset\VoidMethodTypeHintedClass;

/**
 * Test to {@see ProxyManager\Generator\Util\ProxiedMethodReturnExpression}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Generator\Util\ProxiedMethodReturnExpression
 *
 * @group Coverage
 */
class ProxiedMethodReturnExpressionTest extends TestCase
{
    /**
     * @dataProvider returnExpressionsProvider
     *
     * @param string                 $expression
     * @param null|\ReflectionMethod $originalMethod
     * @param string                 $expectedGeneratedCode
     */
    public function testGeneratedReturnExpression(
        string $expression,
        ?\ReflectionMethod $originalMethod,
        string $expectedGeneratedCode
    ) : void {
        self::assertSame($expectedGeneratedCode, ProxiedMethodReturnExpression::generate($expression, $originalMethod));
    }

    public function returnExpressionsProvider() : array
    {
        return [
            'variable, no original method' => [
                '$foo',
                null,
                'return $foo;'
            ],
            'variable, given non-void original method' => [
                '$foo',
                new \ReflectionMethod(self::class, 'returnExpressionsProvider'),
                'return $foo;'
            ],
            'variable, given void original method' => [
                '$foo',
                new \ReflectionMethod(VoidMethodTypeHintedClass::class, 'returnVoid'),
                "\$foo;\nreturn;"
            ],
            'expression, no original method' => [
                '(1 + 1)',
                null,
                'return (1 + 1);'
            ],
            'expression, given non-void original method' => [
                '(1 + 1)',
                new \ReflectionMethod(self::class, 'returnExpressionsProvider'),
                'return (1 + 1);'
            ],
            'expression, given void original method' => [
                '(1 + 1)',
                new \ReflectionMethod(VoidMethodTypeHintedClass::class, 'returnVoid'),
                "(1 + 1);\nreturn;"
            ],
        ];
    }
}
