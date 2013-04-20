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
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract;
use ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract
 */
class ExtractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testSignature()
    {
        $extract    = new Extract(array(), array());
        $parameters = $extract->getParameters();

        $this->assertSame('extract', $extract->getName());
        $this->assertCount(1, $parameters);

        /* @var $objectParam \Zend\Code\Generator\ParameterGenerator */
        $objectParam = reset($parameters);

        $this->assertSame('object', $objectParam->getName());
        $this->assertNull($objectParam->getType());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testGeneratedStructureWithMixedAccessType()
    {
        $publicProperty    = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'publicProperty');
        $protectedProperty = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'protectedProperty');
        $property          = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'privateProperty');
        $accessor          = $this
            ->getMockBuilder('ProxyManager\\ProxyGenerator\\Hydrator\\PropertyGenerator\\PropertyAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        $accessor->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $accessor->expects($this->any())->method('getOriginalProperty')->will($this->returnValue($property));

        $extract = new Extract(array($publicProperty, $protectedProperty), array($accessor));

        $this->assertSame(
            "\$data = (array) \$object;\n\n"
            . "return array(\n"
            . "    'publicProperty' => \$object->publicProperty,\n"
            . "    'protectedProperty' => \$data[\"\\0*\\0protectedProperty\"],\n"
            . "    'privateProperty' => \$data[\"\\0ProxyManagerTestAsset\\BaseClass\\0privateProperty\"],\n"
            . ");",
            $extract->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testGeneratedStructureWithPublicProperties()
    {
        $publicProperty = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'publicProperty');
        $extract        = new Extract(array($publicProperty), array());

        $this->assertSame(
            "return array(\n"
            . "    'publicProperty' => \$object->publicProperty,\n"
            . ");",
            $extract->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract::__construct
     */
    public function testGeneratedStructureWithPublicAndProtectedProperties()
    {
        $publicProperty = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'publicProperty');
        $protectedProperty = new ReflectionProperty('ProxyManagerTestAsset\\BaseClass', 'protectedProperty');
        $extract        = new Extract(array($publicProperty, $protectedProperty), array());

        $this->assertSame(
            "return array(\n"
            . "    'publicProperty' => \$object->publicProperty,\n"
            . "    'protectedProperty' => \$object->protectedProperty,\n"
            . ");",
            $extract->getBody()
        );
    }
}
