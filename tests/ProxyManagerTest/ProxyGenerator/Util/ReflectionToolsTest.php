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

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Util\ReflectionTools;
use ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing;
use ProxyManager\ProxyGenerator\Util\ReflectionTools\FunctionArgumentsParsing;
use ProxyManager\ProxyGenerator\Util\ReflectionTools\MethodArgumentsParsing;
use Zend\Code\Reflection\MethodReflection;
use ReflectionFunction;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\ReflectionTools}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\ReflectionTools
 */
class ReflectionToolsTest extends PHPUnit_Framework_TestCase
{
    public function testArgumentsLi()
    {
        $argReflection = new ReflectionTools();
        
        $parser = $argReflection->getArgumentsLine(array());
        $this->assertTrue($parser instanceof ArrayArgumentsParsing);
        
        $parser = $argReflection->getArgumentsLine(new ReflectionFunction(function() { return 'bar'; }));
        $this->assertTrue($parser instanceof FunctionArgumentsParsing);
        
        $methodReflection   = new MethodReflection('ProxyManagerTestAsset\BaseClass', 'publicMethod');
        $parser = $argReflection->getArgumentsLine($methodReflection);
        $this->assertTrue($parser instanceof MethodArgumentsParsing);
    }
}
