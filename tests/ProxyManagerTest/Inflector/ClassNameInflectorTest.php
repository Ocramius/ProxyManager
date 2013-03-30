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
    public function testGetProxyFileName($realClassName, $proxyClassName)
    {
        $inflector = new ClassNameInflector('ProxyNS');

        $this->assertFalse($inflector->isProxyClassName($realClassName));
        $this->assertTrue($inflector->isProxyClassName($proxyClassName));
        $this->assertSame($realClassName, $inflector->getUserClassName($realClassName));
        $this->assertSame($proxyClassName, $inflector->getProxyClassName($proxyClassName));
        $this->assertSame($proxyClassName, $inflector->getProxyClassName($realClassName));
        $this->assertSame($realClassName, $inflector->getUserClassName($proxyClassName));
    }

    /**
     * @return array
     */
    public function getClassNames()
    {
        return array(
            array('Foo', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo'),
            array('Foo\\Bar', 'ProxyNS\\' . ClassNameInflectorInterface::PROXY_MARKER . '\\Foo\\Bar'),
        );
    }
}
