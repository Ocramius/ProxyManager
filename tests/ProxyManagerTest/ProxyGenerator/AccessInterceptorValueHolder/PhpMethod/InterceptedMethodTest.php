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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\InterceptedMethod;
use ReflectionMethod;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\InterceptedMethod}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InterceptedMethodTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\InterceptedMethod::generateMethod
     */
    public function testBodyStructure()
    {
        $valueHolder        = $this->getMock('CG\\Generator\\PhpProperty');
        $prefixInterceptors = $this->getMock('CG\\Generator\\PhpProperty');
        $suffixInterceptors = $this->getMock('CG\\Generator\\PhpProperty');

        $reflection = new ReflectionMethod('ProxyManagerTestAsset\\BaseClass', 'publicByReferenceParameterMethod');

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));

        /* @var $method \CG\Generator\PhpMethod */
        $method = InterceptedMethod::generateMethod(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        $this->assertInstanceOf('CG\\Generator\\PhpMethod', $method);

        $this->assertSame('publicByReferenceParameterMethod', $method->getName());
        $this->assertCount(2, $method->getParameters());
        $this->assertGreaterThan(
            0,
            strpos(
                $method->getBody(),
                '$returnValue = $this->foo->publicByReferenceParameterMethod($param, $byRefParam);'
            )
        );
    }
}
