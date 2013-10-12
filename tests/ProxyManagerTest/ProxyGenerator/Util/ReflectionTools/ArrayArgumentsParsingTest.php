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
use ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing
 */
class ArrayArgumentsParsingTest extends PHPUnit_Framework_TestCase
{
    public function testArguments()
    {
        $arg = array();
        $this->assertEquals('', ArrayArgumentsParsing::toString($arg));
        $this->assertEquals('void', ArrayArgumentsParsing::toIdentifiableString($arg));
        
        $arg = array('foo');
        $this->assertEquals('$0', ArrayArgumentsParsing::toString($arg));
        $this->assertEquals('$', ArrayArgumentsParsing::toIdentifiableString($arg));
        
        $arg = array('foo', 'bar');
        $this->assertEquals('$0,$1', ArrayArgumentsParsing::toString($arg));
        $this->assertEquals('$,$', ArrayArgumentsParsing::toIdentifiableString($arg));
        
        $arg = array('foo', array());
        $this->assertEquals('$0,array $1', ArrayArgumentsParsing::toString($arg));
        $this->assertEquals('$,array $', ArrayArgumentsParsing::toIdentifiableString($arg));
        
        $arg = array('foo', new \stdClass());
        $this->assertEquals('$0,\stdClass $1', ArrayArgumentsParsing::toString($arg));
        $this->assertEquals('$,\stdClass $', ArrayArgumentsParsing::toIdentifiableString($arg));
    }
    
    public function testArgumentCallable()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('`callable` is only supported in PHP >=5.4.0');
        }
        
        $arg = array('foo', function() { return 'foo'; });
        $this->assertEquals('$0,callable $1', ArrayArgumentsParsing::toString($arg));
        $this->assertEquals('$,callable $', ArrayArgumentsParsing::toIdentifiableString($arg));
    }
}
