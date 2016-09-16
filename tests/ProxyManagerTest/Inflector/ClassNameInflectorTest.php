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

namespace ProxyManagerTest\Inflector;

use PHPUnit_Framework_TestCase;
use ProxyManager\Inflector\ClassNameInflector;
use ProxyManager\Inflector\ClassNameInflectorInterface;

/**
 * Tests for {@see \ProxyManager\Inflector\ClassNameInflector}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ClassNameInflectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getClassNames
     *
     * @covers \ProxyManager\Inflector\ClassNameInflector::__construct
     * @covers \ProxyManager\Inflector\ClassNameInflector::getUserClassName
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     * @covers \ProxyManager\Inflector\ClassNameInflector::isProxyClassName
     *
     * @param string $realClassName
     * @param string $proxyClassName
     */
    public function testInflector(string $realClassName, string $proxyClassName)
    {
        $inflector = new ClassNameInflector('ProxyNS');

        self::assertFalse($inflector->isProxyClassName($realClassName));
        self::assertTrue($inflector->isProxyClassName($proxyClassName));
        self::assertStringMatchesFormat($realClassName, $inflector->getUserClassName($realClassName));
        self::assertStringMatchesFormat($proxyClassName, $inflector->getProxyClassName($proxyClassName));
        self::assertStringMatchesFormat($proxyClassName, $inflector->getProxyClassName($realClassName));
        self::assertStringMatchesFormat($realClassName, $inflector->getUserClassName($proxyClassName));
    }

    /**
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     */
    public function testGeneratesSameClassNameWithSameParameters() : void
    {
        $inflector = new ClassNameInflector('ProxyNS');

        self::assertSame($inflector->getProxyClassName('Foo\\Bar'), $inflector->getProxyClassName('Foo\\Bar'));
        self::assertSame(
            $inflector->getProxyClassName('Foo\\Bar', ['baz' => 'tab']),
            $inflector->getProxyClassName('Foo\\Bar', ['baz' => 'tab'])
        );
        self::assertSame(
            $inflector->getProxyClassName('Foo\\Bar', ['tab' => 'baz']),
            $inflector->getProxyClassName('Foo\\Bar', ['tab' => 'baz'])
        );
    }

    /**
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     */
    public function testGeneratesDifferentClassNameWithDifferentParameters() : void
    {
        $inflector = new ClassNameInflector('ProxyNS');

        self::assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar'),
            $inflector->getProxyClassName('Foo\\Bar', ['foo' => 'bar'])
        );
        self::assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar', ['baz' => 'tab']),
            $inflector->getProxyClassName('Foo\\Bar', ['tab' => 'baz'])
        );
        self::assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar', ['foo' => 'bar', 'tab' => 'baz']),
            $inflector->getProxyClassName('Foo\\Bar', ['foo' => 'bar'])
        );
        self::assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar', ['foo' => 'bar', 'tab' => 'baz']),
            $inflector->getProxyClassName('Foo\\Bar', ['tab' => 'baz', 'foo' => 'bar'])
        );
    }

    /**
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     */
    public function testGeneratesCorrectClassNameWhenGivenLeadingBackslash() : void
    {
        $inflector = new ClassNameInflector('ProxyNS');

        self::assertSame(
            $inflector->getProxyClassName('\\Foo\\Bar', ['tab' => 'baz']),
            $inflector->getProxyClassName('Foo\\Bar', ['tab' => 'baz'])
        );
    }

    /**
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     *
     * @dataProvider getClassAndParametersCombinations
     *
     * @param string $className
     * @param array  $parameters
     */
    public function testClassNameIsValidClassIdentifier(string $className, array $parameters)
    {
        $inflector = new ClassNameInflector('ProxyNS');

        self::assertRegExp(
            '/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)(\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)*/',
            $inflector->getProxyClassName($className, $parameters),
            'Class name string is a valid class identifier'
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function getClassNames() : array
    {
        return [
            ['Foo', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo\\%s'],
            ['Foo\\Bar', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo\\Bar\\%s'],
        ];
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function getClassAndParametersCombinations() : array
    {
        return [
            ['Foo', []],
            ['Foo\\Bar', []],
            ['Foo', [null]],
            ['Foo\\Bar', [null]],
            ['Foo', ['foo' => 'bar']],
            ['Foo\\Bar', ['foo' => 'bar']],
            ['Foo', ["\0" => "very \0 bad"]],
            ['Foo\\Bar', ["\0" => "very \0 bad"]],
        ];
    }
}
