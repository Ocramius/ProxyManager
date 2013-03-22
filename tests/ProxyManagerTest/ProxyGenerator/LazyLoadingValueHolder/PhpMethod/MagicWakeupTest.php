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

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\PhpMethod;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PhpMethod\MagicWakeup;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PhpMethod\MagicWakeup}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicWakeupTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PhpMethod\MagicWakeup::__construct
     */
    public function testBodyStructure()
    {
        $property1       = $this->getMock('ReflectionProperty', array(), array(), '', false);
        $property2       = $this->getMock('ReflectionProperty', array(), array(), '', false);
        $reflectionClass = $this->getMock('ReflectionClass', array(), array(), '', false);

        $property1->expects($this->any())->method('getName')->will($this->returnValue('bar'));
        $property2->expects($this->any())->method('getName')->will($this->returnValue('baz'));

        $reflectionClass
            ->expects($this->any())
            ->method('getProperties')
            ->will($this->returnValue(array($property1, $property2)));

        $constructor = new MagicWakeup($reflectionClass);

        $this->assertSame('__wakeup', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        $this->assertSame("unset(\$this->bar, \$this->baz);", $constructor->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PhpMethod\MagicWakeup::__construct
     */
    public function testBodyStructureWithoutPublicProperties()
    {
        $reflectionClass = $this->getMock('ReflectionClass', array(), array(), '', false);

        $reflectionClass
            ->expects($this->any())
            ->method('getProperties')
            ->will($this->returnValue(array()));

        $constructor = new MagicWakeup($reflectionClass);

        $this->assertSame('__wakeup', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        $this->assertEmpty($constructor->getBody());
    }
}
