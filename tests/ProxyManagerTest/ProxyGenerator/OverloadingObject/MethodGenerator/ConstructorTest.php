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
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\Constructor;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\Constructor}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructure()
    {
        $prototypes = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prototypes->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        
        $reflection  = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\OverloadingObject\\MethodGenerator\\ClassWithTwoMethod'
        );
        
        $constructor = new Constructor($prototypes, $reflection->getMethods());

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        
        $body =   '$closure = function() {return \'foo\';};' . "\n"
                . '$prototype = $this->getPrototypeFromClosure($closure);' . "\n"
                . '$this->foo[\'foo\'][$prototype] = $closure;' . "\n"
                . '$closure = function() {return \'bar\';};' . "\n"
                . '$prototype = $this->getPrototypeFromClosure($closure);' . "\n"
                . '$this->foo[\'bar\'][$prototype] = $closure;' . "\n";
        $this->assertSame($body, $constructor->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructureWithoutMethods()
    {
        $prototypes = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prototypes->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        
        $reflection  = new ReflectionClass(
            'ProxyManagerTestAsset\\EmptyClass'
        );

        $constructor = new Constructor($prototypes, $reflection->getMethods());

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        $this->assertSame("", $constructor->getBody());
    }
}
