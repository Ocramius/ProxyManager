<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset}
 *
 * @group Coverage
 */
class MagicIssetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        /** @var PropertyGenerator|MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PublicPropertiesMap|MockObject $publicProperties */
        $publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $publicProperties->expects(self::any())->method('isEmpty')->will(self::returnValue(false));
        $publicProperties->expects(self::any())->method('getName')->will(self::returnValue('bar'));

        $magicIsset = new MagicIsset($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__isset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, '__isset', array('name' => \$name)"
            . ", \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return isset(\$this->bar->\$name);\n}"
            . '%areturn %s;',
            $magicIsset->getBody()
        );
    }
}
