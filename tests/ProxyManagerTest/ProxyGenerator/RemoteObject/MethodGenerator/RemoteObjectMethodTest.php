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

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use Zend\Code\Reflection\MethodReflection;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class RemoteObjectMethodTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithParameters()
    {
        $adapter = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $adapter->expects($this->any())->method('getName')->will($this->returnValue('adapter'));

        $reflectionMethod = new MethodReflection(
            'ProxyManagerTestAsset\\BaseClass',
            'publicByReferenceParameterMethod'
        );

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass('Zend\\Code\\Generator\\PropertyGenerator')
        );

        $this->assertSame('publicByReferenceParameterMethod', $method->getName());
        $this->assertCount(2, $method->getParameters());
        $this->assertSame(
            '$return = $this->adapter->call(\'Zend\\\Code\\\Generator\\\PropertyGenerator\', '
            . '\'publicByReferenceParameterMethod\', array($param, $byRefParam));'
            . "\n\nreturn \$return;",
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithArrayParameter()
    {
        $adapter = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $adapter->expects($this->any())->method('getName')->will($this->returnValue('adapter'));

        $reflectionMethod = new MethodReflection('ProxyManagerTestAsset\\BaseClass', 'publicArrayHintedMethod');

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass('Zend\\Code\\Generator\\PropertyGenerator')
        );

        $this->assertSame('publicArrayHintedMethod', $method->getName());
        $this->assertCount(1, $method->getParameters());
        $this->assertSame(
            '$return = $this->adapter->call(\'Zend\\\Code\\\Generator\\\PropertyGenerator\', '
            . '\'publicArrayHintedMethod\', array($param));'
            . "\n\nreturn \$return;",
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithoutParameters()
    {
        $adapter = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $adapter->expects($this->any())->method('getName')->will($this->returnValue('adapter'));

        $reflectionMethod = new MethodReflection(__CLASS__, 'testBodyStructureWithoutParameters');

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass('Zend\\Code\\Generator\\PropertyGenerator')
        );

        $this->assertSame('testBodyStructureWithoutParameters', $method->getName());
        $this->assertCount(0, $method->getParameters());
        $this->assertSame(
            '$return = $this->adapter->call(\'Zend\\\Code\\\Generator\\\PropertyGenerator\', '
            . '\'testBodyStructureWithoutParameters\', array());'
            . "\n\nreturn \$return;",
            $method->getBody()
        );
    }
}
