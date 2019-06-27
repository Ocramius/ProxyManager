<?php

declare(strict_types=1);

namespace ProxyManagerTest\Generator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ProxyManagerTestAsset\VoidMethodTypeHintedClass;
use ReflectionMethod;

/**
 * Test to {@see ProxyManager\Generator\Util\ProxiedMethodReturnExpression}
 *
 * @covers \ProxyManager\Generator\Util\ProxiedMethodReturnExpression
 * @group Coverage
 */
final class ProxiedMethodReturnExpressionTest extends TestCase
{
    /**
     * @dataProvider returnExpressionsProvider
     */
    public function testGeneratedReturnExpression(
        string $expression,
        ?ReflectionMethod $originalMethod,
        string $expectedGeneratedCode
    ) : void {
        self::assertSame($expectedGeneratedCode, ProxiedMethodReturnExpression::generate($expression, $originalMethod));
    }

    /**
     * @psalm-return array<string, array{0: string, 1: ReflectionMethod|null, 2: string}>
     */
    public function returnExpressionsProvider() : array
    {
        return [
            'variable, no original method' => [
                '$foo',
                null,
                'return $foo;',
            ],
            'variable, given non-void original method' => [
                '$foo',
                new ReflectionMethod(self::class, 'returnExpressionsProvider'),
                'return $foo;',
            ],
            'variable, given void original method' => [
                '$foo',
                new ReflectionMethod(VoidMethodTypeHintedClass::class, 'returnVoid'),
                "\$foo;\nreturn;",
            ],
            'expression, no original method' => [
                '(1 + 1)',
                null,
                'return (1 + 1);',
            ],
            'expression, given non-void original method' => [
                '(1 + 1)',
                new ReflectionMethod(self::class, 'returnExpressionsProvider'),
                'return (1 + 1);',
            ],
            'expression, given void original method' => [
                '(1 + 1)',
                new ReflectionMethod(VoidMethodTypeHintedClass::class, 'returnVoid'),
                "(1 + 1);\nreturn;",
            ],
        ];
    }
}
