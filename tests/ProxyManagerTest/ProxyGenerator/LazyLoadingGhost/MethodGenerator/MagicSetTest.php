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
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructure()
    {
        $reflection  = new ReflectionClass('ProxyManagerTestAsset\\EmptyClass');
        $initializer = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $magicSet = new MagicSet($reflection, $initializer);

        $this->assertSame('__set', $magicSet->getName());
        $this->assertCount(2, $magicSet->getParameters());
        $this->assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this, "
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (in_array(\$name, array())) {\n    return (\$this->\$name = \$value);\n}\n\n"
            . "return (\$this->\$name = \$value);",
            $magicSet->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructureWithPublicProperties()
    {
        $initializer = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $reflection  = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\LazyLoading\\MethodGenerator\\ClassWithTwoPublicProperties'
        );

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $magicSet = new MagicSet($reflection, $initializer);

        $this->assertSame('__set', $magicSet->getName());
        $this->assertCount(2, $magicSet->getParameters());
        $this->assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this, "
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (in_array(\$name, array('bar', 'baz'))) {\n    return (\$this->\$name = \$value);\n}\n\n"
            . "return (\$this->\$name = \$value);",
            $magicSet->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructureWithOverriddenMagicGet()
    {
        $initializer = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');
        $reflection  = new ReflectionClass('ProxyManagerTestAsset\\ClassWithMagicMethods');

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $magicSet = new MagicSet($reflection, $initializer);

        $this->assertSame('__set', $magicSet->getName());
        $this->assertCount(2, $magicSet->getParameters());
        $this->assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this, "
            . "'__set', array('name' => \$name, 'value' => \$value), \$this->foo);\n\n"
            . "if (in_array(\$name, array())) {\n    return (\$this->\$name = \$value);\n}\n\n"
            . "return parent::__set(\$name, \$value);",
            $magicSet->getBody()
        );
    }
}
