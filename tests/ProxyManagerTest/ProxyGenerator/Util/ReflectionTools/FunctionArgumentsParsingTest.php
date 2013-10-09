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

namespace ProxyManagerTest\ProxyGenerator\Util\ReflectionTools;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Util\ReflectionTools\FunctionArgumentsParsing;
use ReflectionFunction;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\ReflectionTools\FunctionArgumentsParsing}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\ReflectionTools\FunctionArgumentsParsing
 */
class FunctionArgumentsParsingTest extends PHPUnit_Framework_TestCase
{
    public function testArgumentsLi()
    {
        $argReflection = new FunctionArgumentsParsing(new ReflectionFunction(function() {}));
        $this->assertEquals('', $argReflection->toString());
        $this->assertEquals('void', $argReflection->toIdentifiableString());
        
        $argReflection = new FunctionArgumentsParsing(new ReflectionFunction(function($foo) {}));
        $this->assertEquals('$foo', $argReflection->toString());
        $this->assertEquals('$', $argReflection->toIdentifiableString());
        
        $argReflection = new FunctionArgumentsParsing(new ReflectionFunction(function($foo, $bar) {}));
        $this->assertEquals('$foo,$bar', $argReflection->toString());
        $this->assertEquals('$,$', $argReflection->toIdentifiableString());
        
        $argReflection = new FunctionArgumentsParsing(new ReflectionFunction(function($foo, array $bar) {}));
        $this->assertEquals('$foo,array $bar', $argReflection->toString());
        $this->assertEquals('$,array $', $argReflection->toIdentifiableString());
        
        $argReflection = new FunctionArgumentsParsing(new ReflectionFunction(function($foo, \stdClass $bar) {}));
        $this->assertEquals('$foo,\stdClass $bar', $argReflection->toString());
        $this->assertEquals('$,\stdClass $', $argReflection->toIdentifiableString());
    }
}
