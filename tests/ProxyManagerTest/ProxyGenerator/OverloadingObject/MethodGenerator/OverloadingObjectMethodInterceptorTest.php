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

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor;
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
    public function testBodyStructure()
    {
        $reflection = new MethodReflection('ProxyManagerTestAsset\\BaseClass', 'publicArrayHintedMethod');
        $method     = OverloadingObjectMethodInterceptor::generateMethod($reflection);

        $this->assertSame('publicArrayHintedMethod', $method->getName());
        $this->assertCount(1, $method->getParameters());
        $this->assertSame(
              '$args = func_get_args();' . "\n"
            . 'return $this->__call(__FUNCTION__, $args);',
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor
     */
    public function testBodyStructureWithReferenceReturn()
    {
        $reflection = new MethodReflection('ProxyManagerTestAsset\\BaseClass', 'publicByReferenceMethod');
        $method = OverloadingObjectMethodInterceptor::generateMethod($reflection);

        $this->assertSame('publicByReferenceMethod', $method->getName());
        $this->assertCount(0, $method->getParameters());
        $this->assertSame(
            '$args = func_get_args();' . "\n"
            . '$return = $this->__call(__FUNCTION__, $args);' . "\n"
            . 'return $return;',
            $method->getBody()
        );
    }
}
