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
     */
    public function testInflector($realClassName, $proxyClassName)
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertFalse($inflector->isProxyClassName($realClassName));
        $this->assertTrue($inflector->isProxyClassName($proxyClassName));
        $this->assertStringMatchesFormat($realClassName, $inflector->getUserClassName($realClassName));
        $this->assertStringMatchesFormat($proxyClassName, $inflector->getProxyClassName($proxyClassName));
        $this->assertStringMatchesFormat($proxyClassName, $inflector->getProxyClassName($realClassName));
        $this->assertStringMatchesFormat($realClassName, $inflector->getUserClassName($proxyClassName));
    }

    /**
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     */
    public function testGeneratesSameClassNameWithSameParameters()
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertSame($inflector->getProxyClassName('Foo\\Bar'), $inflector->getProxyClassName('Foo\\Bar'));
        $this->assertSame(
            $inflector->getProxyClassName('Foo\\Bar', array('baz' => 'tab')),
            $inflector->getProxyClassName('Foo\\Bar', array('baz' => 'tab'))
        );
        $this->assertSame(
            $inflector->getProxyClassName('Foo\\Bar', array('tab' => 'baz')),
            $inflector->getProxyClassName('Foo\\Bar', array('tab' => 'baz'))
        );
    }

    /**
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     */
    public function testGeneratesDifferentClassNameWithDifferentParameters()
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar'),
            $inflector->getProxyClassName('Foo\\Bar', array('foo' => 'bar'))
        );
        $this->assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar', array('baz' => 'tab')),
            $inflector->getProxyClassName('Foo\\Bar', array('tab' => 'baz'))
        );
        $this->assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar', array('foo' => 'bar', 'tab' => 'baz')),
            $inflector->getProxyClassName('Foo\\Bar', array('foo' => 'bar'))
        );
        $this->assertNotSame(
            $inflector->getProxyClassName('Foo\\Bar', array('foo' => 'bar', 'tab' => 'baz')),
            $inflector->getProxyClassName('Foo\\Bar', array('tab' => 'baz', 'foo' => 'bar'))
        );
    }

    /**
     * @covers \ProxyManager\Inflector\ClassNameInflector::getProxyClassName
     */
    public function testGeneratesCorrectClassNameWhenGivenLeadingBackslash()
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertSame(
            $inflector->getProxyClassName('\\Foo\\Bar', array('tab' => 'baz')),
            $inflector->getProxyClassName('Foo\\Bar', array('tab' => 'baz'))
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
    public function testClassNameIsValidClassIdentifier($className, array $parameters)
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertRegExp(
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
    public function getClassNames()
    {
        return array(
            array('Foo', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo\\%s'),
            array('Foo\\Bar', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo\\Bar\\%s'),
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function getClassAndParametersCombinations()
    {
        return array(
            array('Foo', array()),
            array('Foo\\Bar', array()),
            array('Foo', array(null)),
            array('Foo\\Bar', array(null)),
            array('Foo', array('foo' => 'bar')),
            array('Foo\\Bar', array('foo' => 'bar')),
            array('Foo', array("\0" => "very \0 bad")),
            array('Foo\\Bar', array("\0" => "very \0 bad")),
        );
    }
}
