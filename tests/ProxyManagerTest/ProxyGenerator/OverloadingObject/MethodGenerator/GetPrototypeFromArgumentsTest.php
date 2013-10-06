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
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromArguments;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromArguments}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class GetPrototypeFromArgumentsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromArguments::__construct
     */
    public function testBodyStructure()
    {
        $prototypes = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prototypes->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        
        $reflection  = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\OverloadingObject\\MethodGenerator\\ClassWithSomeMethods'
        );
        
        $method = new GetPrototypeFromArguments();

        $this->assertSame('getPrototypeFromArguments', $method->getName());
        $this->assertCount(1, $method->getParameters());
        
        $getPrototypeFromArguments = function($arguments) use ($method) {
            eval($method->getBody());
            return $prototype;
        };
        
        $this->assertEquals(
            'void',
            $getPrototypeFromArguments(array())
        );
        
        $this->assertEquals(
            'array $0',
            $getPrototypeFromArguments(array(array('foo')))
        );
        
        $this->assertEquals(
            '$0,$1',
            $getPrototypeFromArguments(array('foo', 'bar'))
        );
        
        $this->assertEquals(
            '$0,array $1',
            $getPrototypeFromArguments(array('foo', array()))
        );
        
        $this->assertEquals(
            '$0,ProxyManagerTestAsset\OverloadingObjectMock $1',
            $getPrototypeFromArguments(array('foo', new \ProxyManagerTestAsset\OverloadingObjectMock()))
        );
        
        $this->assertEquals(
            '$0,stdClass $1',
            $getPrototypeFromArguments(array('foo', new \stdClass()))
        );
    }
}
