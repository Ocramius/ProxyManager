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

declare(strict_types=1);

namespace ProxyManagerTest\Autoloader;

use PHPUnit_Framework_TestCase;
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
class AutoloaderTest extends PHPUnit_Framework_TestCase
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

        self::assertFalse($this->autoloader->__invoke($className));
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

        self::assertFalse($this->autoloader->__invoke($className));
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadExistingClass() : void
    {
        self::assertFalse($this->autoloader->__invoke(__CLASS__));
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

        self::assertTrue($this->autoloader->__invoke($fqcn));
        self::assertTrue(class_exists($fqcn, false));
    }
}
