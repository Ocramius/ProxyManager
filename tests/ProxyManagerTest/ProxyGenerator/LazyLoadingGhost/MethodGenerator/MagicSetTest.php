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

use ReflectionClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicSetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\Code\Generator\PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializer;

    /**
     * @var \ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $publicProperties;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->initializer      = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $this->publicProperties = $this
            ->getMockBuilder('ProxyManager\\ProxyGenerator\\PropertyGenerator\\PublicPropertiesMap')
            ->disableOriginalConstructor()
            ->getMock();

        $this->initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));
        $this->publicProperties->expects($this->any())->method('isEmpty')->will($this->returnValue(false));
        $this->publicProperties->expects($this->any())->method('getName')->will($this->returnValue('bar'));
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructure()
    {
        $reflection = new ReflectionClass('ProxyManagerTestAsset\\EmptyClass');
        $magicSet   = new MagicSet($reflection, $this->initializer, $this->publicProperties);

        $this->assertSame('__set', $magicSet->getName());
        $this->assertCount(2, $magicSet->getParameters());
        $this->assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this, "
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return (\$this->\$name = \$value);\n}\n\n"
            . "%areturn %s;",
            $magicSet->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructureWithPublicProperties()
    {
        $reflection = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\LazyLoading\\MethodGenerator\\ClassWithTwoPublicProperties'
        );

        $magicSet   = new MagicSet($reflection, $this->initializer, $this->publicProperties);

        $this->assertSame('__set', $magicSet->getName());
        $this->assertCount(2, $magicSet->getParameters());
        $this->assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this, "
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return (\$this->\$name = \$value);\n}\n\n"
            . "%areturn %s;",
            $magicSet->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructureWithOverriddenMagicGet()
    {
        $reflection = new ReflectionClass('ProxyManagerTestAsset\\ClassWithMagicMethods');
        $magicSet   = new MagicSet($reflection, $this->initializer, $this->publicProperties);

        $this->assertSame('__set', $magicSet->getName());
        $this->assertCount(2, $magicSet->getParameters());
        $this->assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this, "
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return (\$this->\$name = \$value);\n}\n\n"
            . "return parent::__set(\$name, \$value);",
            $magicSet->getBody()
        );
    }
}
