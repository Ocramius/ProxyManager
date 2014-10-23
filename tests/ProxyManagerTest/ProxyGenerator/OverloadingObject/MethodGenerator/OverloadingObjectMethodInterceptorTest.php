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

namespace ProxyManagerTest\ProxyGenerator\OverloadingObject\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor;
use ProxyManager\ProxyGenerator\OverloadingObject\PropertyGenerator\PrototypesProperty;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class OverloadingObjectMethodInterceptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor
     */
    public function testBodyStructureWitNoAdditionalMethod()
    {
        $reflection    = new MethodReflection('ProxyManagerTestAsset\\BaseClass', 'publicArrayHintedMethod');
        $property      = new PrototypesProperty();
        $prototypeName = OverloadingObjectMethodInterceptor::getPrototypeName();
        $method        = OverloadingObjectMethodInterceptor::generateMethod($property, $reflection, array());

        $this->assertSame('publicArrayHintedMethod', $method->getName());
        $this->assertCount(1, $method->getParameters());
        $this->assertSame(
              '$self = $this;' . "\n"
            . '$args = func_get_args();' . "\n"
            . $prototypeName . ' = \ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing::toIdentifiableString($args);' . "\n"
            . 'if (' . $prototypeName .' == "array $") {' . "\n"
            . '        return \'publicArrayHintedMethodDefault\';' . "\n"
            . '}' . "\n"
            . 'else if (isset($this->' . $property->getName() . '["publicArrayHintedMethod"][' . $prototypeName .'])) {' . "\n"
            . '    return call_user_func_array($this->' . $property->getName() . '["publicArrayHintedMethod"][' . $prototypeName .'], $args);' . "\n"
            . '} else {' . "\n"
            . '    trigger_error("Call to undefined method publicArrayHintedMethod", E_USER_ERROR);' . "\n"
            . '}',
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor
     */
    public function testBodyStructureWitAdditionalMethod()
    {
        $reflection    = new MethodReflection('ProxyManagerTestAsset\\BaseClass', 'publicByReferenceMethod');
        $property      = new PrototypesProperty();
        $prototypeName = OverloadingObjectMethodInterceptor::getPrototypeName();
        $method        = OverloadingObjectMethodInterceptor::generateMethod($property, $reflection, array(
            function($foo, $bar) { return $foo . $bar; }
        ));

        $this->assertSame('publicByReferenceMethod', $method->getName());
        $this->assertCount(0, $method->getParameters());
        $this->assertSame(
              '$self = $this;' . "\n"
            . '$args = func_get_args();' . "\n"
            . $prototypeName . ' = \ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing::toIdentifiableString($args);' . "\n"
            . 'if (' . $prototypeName .' == "void") {' . "\n"
            . '        $returnValue = \'publicByReferenceMethodDefault\';' . "\n"
            . '' . "\n"
            . '        return $returnValue;' . "\n"
            . '}' . "\n"
            . 'else if (' . $prototypeName .' === \'$,$\') {' . "\n"
            . '$foo = $args[0];' . "\n"
            . '$bar = $args[1];' . "\n"
            . ' return $foo . $bar;' . "\n"
            . '}' . "\n"
            . 'else if (isset($this->' . $property->getName() . '["publicByReferenceMethod"][' . $prototypeName .'])) {' . "\n"
            . '    return call_user_func_array($this->' . $property->getName() . '["publicByReferenceMethod"][' . $prototypeName .'], $args);' . "\n"
            . '} else {' . "\n"
            . '    trigger_error("Call to undefined method publicByReferenceMethod", E_USER_ERROR);' . "\n"
            . '}',
            $method->getBody()
        );
    }
}
