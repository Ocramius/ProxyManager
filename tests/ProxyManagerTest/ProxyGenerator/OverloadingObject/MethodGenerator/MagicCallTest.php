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
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\MagicCall;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\MagicCall}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class MagicCallTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\MagicCall::__construct
     */
    public function testBodyStructure()
    {
        $prototypes = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prototypes->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        
        $reflection  = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\OverloadingObject\\MethodGenerator\\ClassWithSomeMethods'
        );
        
        $call = new MagicCall($reflection, $prototypes);

        $this->assertSame('__call', $call->getName());
        $this->assertCount(2, $call->getParameters());
        
        $body =   '$prototype = $this->getPrototypeFromArguments($arguments);' . "\n"
                . 'if (isset($this->foo[$name][$prototype])) {' . "\n"
                . '    return call_user_func_array($this->foo[$name][$prototype], $arguments);' . "\n"
                . '}';
        $this->assertSame($body, $call->getBody());
    }
}
