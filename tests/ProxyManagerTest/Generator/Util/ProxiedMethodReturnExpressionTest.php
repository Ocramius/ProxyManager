<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace ProxyManagerTest\Generator\Util;

use PHPUnit_Framework_TestCase;
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
class ProxiedMethodReturnExpressionTest extends PHPUnit_Framework_TestCase
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
