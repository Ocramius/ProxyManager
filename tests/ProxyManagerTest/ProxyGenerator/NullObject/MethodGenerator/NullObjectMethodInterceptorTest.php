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

namespace ProxyManagerTest\ProxyGenerator\NullObject\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class NullObjectMethodInterceptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructure()
    {
        $reflection = new MethodReflection('ProxyManagerTestAsset\\BaseClass', 'publicByReferenceParameterMethod');
        $method     = NullObjectMethodInterceptor::generateMethod($reflection);

        $this->assertSame('publicByReferenceParameterMethod', $method->getName());
        $this->assertCount(2, $method->getParameters());
        $this->assertSame("", $method->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructureWithoutParameters()
    {
        $reflectionMethod = new MethodReflection(__CLASS__, 'testBodyStructureWithoutParameters');

        $method = NullObjectMethodInterceptor::generateMethod($reflectionMethod);

        $this->assertSame('testBodyStructureWithoutParameters', $method->getName());
        $this->assertCount(0, $method->getParameters());
        $this->assertSame("", $method->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor
     */
    public function testBodyStructureWithoutByRefReturn()
    {
        $reflectionMethod = new MethodReflection('ProxyManagerTestAsset\BaseClass', 'publicByReferenceMethod');

        $method = NullObjectMethodInterceptor::generateMethod($reflectionMethod);

        $this->assertSame('publicByReferenceMethod', $method->getName());
        $this->assertCount(0, $method->getParameters());
        $this->assertStringMatchesFormat("\$ref%s = null;\nreturn \$ref%s;", $method->getBody());
    }
}
