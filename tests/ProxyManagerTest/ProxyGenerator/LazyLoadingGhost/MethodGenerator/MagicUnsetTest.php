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

use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicUnsetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializer;

    /**
     * @var MethodGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initMethod;

    /**
     * @var PublicPropertiesMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $publicProperties;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initializer      = $this->getMock(PropertyGenerator::class);
        $this->initMethod       = $this->getMock(MethodGenerator::class);
        $this->publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $this->initMethod->expects($this->any())->method('getName')->will($this->returnValue('baz'));
        $this->publicProperties->expects($this->any())->method('isEmpty')->will($this->returnValue(false));
        $this->publicProperties->expects($this->any())->method('getName')->will($this->returnValue('bar'));
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructure()
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        $magicIsset = new MagicUnset($reflection, $this->initializer, $this->initMethod, $this->publicProperties);

        $this->assertSame('__unset', $magicIsset->getName());
        $this->assertCount(1, $magicIsset->getParameters());
        $this->assertStringMatchesFormat(
            "\$this->foo && \$this->baz('__unset', array('name' => \$name));\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    unset(\$this->\$name);\n\n    return;\n}"
            . "%areturn %s;",
            $magicIsset->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructureWithPublicProperties()
    {
        $reflection = new ReflectionClass(
            ClassWithTwoPublicProperties::class
        );

        $magicIsset = new MagicUnset($reflection, $this->initializer, $this->initMethod, $this->publicProperties);

        $this->assertSame('__unset', $magicIsset->getName());
        $this->assertCount(1, $magicIsset->getParameters());
        $this->assertStringMatchesFormat(
            "\$this->foo && \$this->baz('__unset', array('name' => \$name));\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    unset(\$this->\$name);\n\n    return;\n}"
            . "%areturn %s;",
            $magicIsset->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructureWithOverriddenMagicGet()
    {
        $reflection = new ReflectionClass(ClassWithMagicMethods::class);
        $magicIsset = new MagicUnset($reflection, $this->initializer, $this->initMethod, $this->publicProperties);

        $this->assertSame('__unset', $magicIsset->getName());
        $this->assertCount(1, $magicIsset->getParameters());
        $this->assertSame(
            "\$this->foo && \$this->baz('__unset', array('name' => \$name));\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    unset(\$this->\$name);\n\n    return;\n}\n\n"
            . "return parent::__unset(\$name);",
            $magicIsset->getBody()
        );
    }
}
