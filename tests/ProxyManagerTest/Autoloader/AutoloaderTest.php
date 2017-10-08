<?php

declare(strict_types=1);

namespace ProxyManagerTest\Autoloader;

use PHPUnit\Framework\TestCase;
use ProxyManager\Autoloader\Autoloader;
use ProxyManager\FileLocator\FileLocatorInterface;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\Inflector\ClassNameInflectorInterface;

/**
 * Tests for {@see \ProxyManager\Autoloader\Autoloader}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Autoloader\Autoloader
 * @group Coverage
 */
class AutoloaderTest extends TestCase
{
    /**
     * @var \ProxyManager\Autoloader\Autoloader
     */
    protected $autoloader;

    /**
     * @var \ProxyManager\FileLocator\FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileLocator;

    /**
     * @var \ProxyManager\Inflector\ClassNameInflectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classNameInflector;

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__construct
     */
    public function setUp()
    {
        $this->fileLocator        = $this->createMock(FileLocatorInterface::class);
        $this->classNameInflector = $this->createMock(ClassNameInflectorInterface::class);
        $this->autoloader         = new Autoloader($this->fileLocator, $this->classNameInflector);
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadUserClasses() : void
    {
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');
        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isProxyClassName')
            ->with($className)
            ->will(self::returnValue(false));

        self::assertFalse($this->autoloadWithoutFurtherAutoloaders($className));
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadNonExistingClass() : void
    {
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');
        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isProxyClassName')
            ->with($className)
            ->will(self::returnValue(true));
        $this
            ->fileLocator
            ->expects(self::once())
            ->method('getProxyFileName')
            ->will(self::returnValue(__DIR__ . '/non-existing'));

        self::assertFalse($this->autoloadWithoutFurtherAutoloaders($className));
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadExistingClass() : void
    {
        self::assertFalse($this->autoloadWithoutFurtherAutoloaders(__CLASS__));
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillAutoloadExistingFile() : void
    {
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;
        $fileName  = sys_get_temp_dir() . '/foo_' . uniqid() . '.php';

        file_put_contents($fileName, '<?php namespace ' . $namespace . '; class ' . $className . '{}');

        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isProxyClassName')
            ->with($fqcn)
            ->will(self::returnValue(true));
        $this
            ->fileLocator
            ->expects(self::once())
            ->method('getProxyFileName')
            ->will(self::returnValue($fileName));

        self::assertTrue($this->autoloadWithoutFurtherAutoloaders($fqcn));
        self::assertTrue(class_exists($fqcn, false));
    }

    private function autoloadWithoutFurtherAutoloaders(string $className) : bool
    {
        $failingAutoloader = null;
        $failingAutoloader = function (string $className) use (& $failingAutoloader) : void {
            spl_autoload_unregister($failingAutoloader);

            $this->fail(sprintf('Fallback autoloading was triggered to load "%s"', $className));
        };

        spl_autoload_register($failingAutoloader);

        $result = $this->autoloader->__invoke($className);

        spl_autoload_unregister($failingAutoloader);

        return $result;
    }
}
