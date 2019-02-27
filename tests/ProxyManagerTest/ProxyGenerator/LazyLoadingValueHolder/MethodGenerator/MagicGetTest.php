<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet}
 *
 * @group Coverage
 */
class MagicGetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet::__construct
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

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');
        $publicProperties->method('isEmpty')->willReturn(false);
        $publicProperties->method('getName')->willReturn('bar');

        $magicGet = new MagicGet($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());
        self::assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, '__get', ['name' => \$name]"
            . ", \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return \$this->bar->\$name;\n}"
            . '%areturn %s;',
            $magicGet->getBody()
        );
        self::assertStringNotMatchesFormat('%Areturn $this->bar->__get($name);%A', $magicGet->getBody());
    }

    /**
     * @group 344
     */
    public function testBodyStructureWithPreExistingGetMethod() : void
    {
        $reflection = new ReflectionClass(ClassWithMagicMethods::class);
        /** @var PropertyGenerator|MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PublicPropertiesMap|MockObject $publicProperties */
        $publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');
        $publicProperties->method('isEmpty')->willReturn(false);
        $publicProperties->method('getName')->willReturn('bar');

        $magicGet = new MagicGet($reflection, $initializer, $valueHolder, $publicProperties);

        self::assertSame('__get', $magicGet->getName());
        self::assertCount(1, $magicGet->getParameters());

        self::assertStringMatchesFormat(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, '__get', ['name' => \$name]"
            . ", \$this->foo);\n\n"
            . "if (isset(self::\$bar[\$name])) {\n    return \$this->bar->\$name;\n}\n\n"
            . 'return $this->bar->__get($name);',
            $magicGet->getBody(),
            'The pre-existing magic `__get` is called, if the property is not accessible'
        );
    }
}
