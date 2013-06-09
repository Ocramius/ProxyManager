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

namespace ProxyManagerTest\ProxyGenerator\Hydrator\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Hydrate;
use ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Hydrate}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Hydrate
 */
class HydrateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Hydrate::__construct
     */
    public function testGeneratedStructure()
    {
        $publicProperty = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'publicProperty');
        $property       = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'privateProperty');
        $accessor       = $this
            ->getMockBuilder('ProxyManager\\ProxyGenerator\\Hydrator\\PropertyGenerator\\PropertyAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        $accessor->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $accessor->expects($this->any())->method('getOriginalProperty')->will($this->returnValue($property));

        $hydrate    = new Hydrate(array($publicProperty), array($accessor));
        $parameters = $hydrate->getParameters();

        $this->assertSame('hydrate', $hydrate->getName());
        $this->assertSame(
            "\$object->publicProperty = \$data['publicProperty'];\n"
            . "\$this->foo->setValue(\$object, \$data['privateProperty']);\n\nreturn \$object;",
            $hydrate->getBody()
        );

        $this->assertCount(2, $parameters);

        /* @var $dataParam \Zend\Code\Generator\ParameterGenerator */
        $dataParam   = reset($parameters);
        /* @var $objectParam \Zend\Code\Generator\ParameterGenerator */
        $objectParam = end($parameters);

        $this->assertSame('data', $dataParam->getName());
        $this->assertSame('array', $dataParam->getType());
        $this->assertSame('object', $objectParam->getName());
        $this->assertNull($objectParam->getType());
    }
}
