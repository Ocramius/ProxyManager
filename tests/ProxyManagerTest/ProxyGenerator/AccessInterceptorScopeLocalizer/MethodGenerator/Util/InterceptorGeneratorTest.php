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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class InterceptorGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator
     */
    public function testInterceptorGenerator()
    {
        $method             = $this->getMock('ProxyManager\\Generator\\MethodGenerator');
        $bar                = $this->getMock('ProxyManager\\Generator\\ParameterGenerator');
        $baz                = $this->getMock('ProxyManager\\Generator\\ParameterGenerator');
        $prefixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $suffixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $bar->expects($this->any())->method('getName')->will($this->returnValue('bar'));
        $baz->expects($this->any())->method('getName')->will($this->returnValue('baz'));
        $method->expects($this->any())->method('getName')->will($this->returnValue('fooMethod'));
        $method->expects($this->any())->method('getParameters')->will($this->returnValue(array($bar, $baz)));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));

        $body = InterceptorGenerator::createInterceptedMethodBody(
            '$returnValue = "foo";',
            $method,
            $prefixInterceptors,
            $suffixInterceptors
        );

        $this->assertSame(
            'if (isset($this->pre[\'fooMethod\'])) {' . "\n"
            . '    $returnEarly       = false;' . "\n"
            . '    $prefixReturnValue = $this->pre[\'fooMethod\']->__invoke($this, $this, \'fooMethod\', '
            . 'array(\'bar\' => $bar, \'baz\' => $baz), $returnEarly);' . "\n\n"
            . '    if ($returnEarly) {' . "\n"
            . '        return $prefixReturnValue;' . "\n"
            . '    }' . "\n"
            . '}' . "\n\n"
            . '$returnValue = "foo";' . "\n\n"
            . 'if (isset($this->post[\'fooMethod\'])) {' . "\n"
            . '    $returnEarly       = false;' . "\n"
            . '    $suffixReturnValue = $this->post[\'fooMethod\']->__invoke($this, $this, \'fooMethod\', '
            . 'array(\'bar\' => $bar, \'baz\' => $baz), $returnValue, $returnEarly);' . "\n\n"
            . '    if ($returnEarly) {' . "\n"
            . '        return $suffixReturnValue;' . "\n"
            . '    }' . "\n"
            . '}' . "\n\n"
            . 'return $returnValue;',
            $body
        );
    }
}
