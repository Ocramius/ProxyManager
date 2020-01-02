<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset}
 *
 * @group Coverage
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset
 */
final class MagicUnsetTest extends TestCase
{
    public function testBodyStructure() : void
    {
        $reflection       = new ReflectionClass(EmptyClass::class);
        $initializer      = $this->createMock(PropertyGenerator::class);
        $valueHolder      = $this->createMock(PropertyGenerator::class);
        $publicProperties = $this->createMock(PublicPropertiesMap::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');
        $publicProperties->method('isEmpty')->willReturn(false);
        $publicProperties->method('getName')->willReturn('bar');

        $magicIsset = new MagicUnset($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__unset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, '__unset', array('name' => \$name)"
            . ", \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    unset(\$this->bar->\$name);\n\n    return;\n}"
            . '%areturn %s;',
            $magicIsset->getBody()
        );
    }

    /**
     * @group 344
     */
    public function testBodyStructureWithPreExistingMagicMethod() : void
    {
        $reflection       = new ReflectionClass(ClassWithMagicMethods::class);
        $initializer      = $this->createMock(PropertyGenerator::class);
        $valueHolder      = $this->createMock(PropertyGenerator::class);
        $publicProperties = $this->createMock(PublicPropertiesMap::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');
        $publicProperties->method('isEmpty')->willReturn(false);
        $publicProperties->method('getName')->willReturn('bar');

        $magicIsset = new MagicUnset($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__unset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, '__unset', array('name' => \$name)"
            . ", \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    unset(\$this->bar->\$name);\n\n    return;\n}\n\n"
            . 'return $this->bar->__unset($name);',
            $magicIsset->getBody(),
            'The pre-existing magic `__unset` is called, if the property is not accessible'
        );
    }
}
