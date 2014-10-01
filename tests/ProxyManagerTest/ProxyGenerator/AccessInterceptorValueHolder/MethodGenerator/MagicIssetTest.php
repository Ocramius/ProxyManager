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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use ReflectionClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicIssetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure()
    {
        $reflection         = new ReflectionClass('ProxyManagerTestAsset\\EmptyClass');
        $valueHolder        = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $prefixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $suffixInterceptors = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $publicProperties   = $this
            ->getMockBuilder('ProxyManager\\ProxyGenerator\\PropertyGenerator\\PublicPropertiesMap')
            ->disableOriginalConstructor()
            ->getMock();

        $valueHolder->expects($this->any())->method('getName')->will($this->returnValue('bar'));
        $prefixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('pre'));
        $suffixInterceptors->expects($this->any())->method('getName')->will($this->returnValue('post'));
        $publicProperties->expects($this->any())->method('isEmpty')->will($this->returnValue(false));

        $magicIsset = new MagicIsset(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $publicProperties
        );

        $this->assertSame('__isset', $magicIsset->getName());
        $this->assertCount(1, $magicIsset->getParameters());
        $this->assertGreaterThan(0, strpos($magicIsset->getBody(), '$returnValue = isset($this->bar->$name);'));
    }
}
