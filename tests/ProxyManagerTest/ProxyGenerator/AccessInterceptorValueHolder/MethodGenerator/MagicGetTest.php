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

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicGetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet::__construct
     */
    public function testBodyStructure()
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->getMock(PropertyGenerator::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->getMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->getMock(PropertyGenerator::class);
        /* @var $publicProperties PublicPropertiesMap|\PHPUnit_Framework_MockObject_MockObject */
        $publicProperties   = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('bar'));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));
        $publicProperties->expects($this->any())->method('isEmpty')->will($this->returnValue(false));

        $magicGet = new MagicGet(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $publicProperties
        );

        $this->assertSame('__get', $magicGet->getName());
        $this->assertCount(1, $magicGet->getParameters());
        $this->assertStringMatchesFormat('%A$returnValue = & $this->bar->$name;%A', $magicGet->getBody());
    }
}
