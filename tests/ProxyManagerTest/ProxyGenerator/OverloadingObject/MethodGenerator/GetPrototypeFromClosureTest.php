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
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromClosure;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromClosureTest}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class GetPrototypeFromClosureTestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromClosureTest::__construct
     */
    public function testBodyStructure()
    {
        $prototypes = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prototypes->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        
        $reflection  = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\OverloadingObject\\MethodGenerator\\ClassWithTwoMethod'
        );
        
        $method = new GetPrototypeFromClosure();

        $this->assertSame('getPrototypeFromClosure', $method->getName());
        $this->assertCount(1, $method->getParameters());
        
        $getPrototypeFromClosure = function($closure) use ($method) {
            eval($method->getBody());
            return $prototype;
        };
        
        $this->assertEquals(
            'void',
            $getPrototypeFromClosure(function() { return; })
        );
        
        $this->assertEquals(
            'array $0',
            $getPrototypeFromClosure(function(array $foo) { return; })
        );
        
        $this->assertEquals(
            '$0,$1',
            $getPrototypeFromClosure(function($foo, $bar) { return; })
        );
        
        $this->assertEquals(
            '$0,array $1',
            $getPrototypeFromClosure(function($foo, array $bar) { return; })
        );
        
        $this->assertEquals(
            '$0,ProxyManagerTestAsset\OverloadingObjectMock $1',
            $getPrototypeFromClosure(function($foo, \ProxyManagerTestAsset\OverloadingObjectMock $bar) { return; })
        );
        
        $this->assertEquals(
            '$0,stdClass $1',
            $getPrototypeFromClosure(function($foo, \stdClass $bar) { return; })
        );
    }
}
