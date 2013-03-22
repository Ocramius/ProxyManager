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

namespace ProxyManagerTest\Autoloader;

use CG\Core\NamingStrategyInterface;
use PHPUnit_Framework_TestCase;
use ProxyManager\Autoloader\Autoloader;

/**
 * Tests for {@see \ProxyManager\Autoloader\Autoloader}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
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
     * @covers \ProxyManager\Autoloader\Autoloader::__construct
     */
    public function setUp()
    {
        $this->fileLocator = $this->getMock('ProxyManager\\FileLocator\\FileLocatorInterface');
        $this->autoloader  = new Autoloader($this->fileLocator);
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadUserClasses()
    {
        $this->assertFalse($this->autoloader->__invoke('foo' . uniqid()));
        $this->assertFalse($this->autoloader->__invoke('some\\bar' . uniqid()));
        $this->assertFalse($this->autoloader->__invoke('some_bar' . uniqid()));
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadNonExistingFiles()
    {
        $this
            ->fileLocator
            ->expects($this->once())
            ->method('getProxyFileName')
            ->will($this->returnValue(__DIR__ . '/non-existing'));

        $this->assertFalse(
            $this->autoloader->__invoke('Foo\\' . NamingStrategyInterface::SEPARATOR . '\\Bar' . uniqid())
        );
    }

    /**
     * @covers \ProxyManager\Autoloader\Autoloader::__invoke
     */
    public function testWillAutoloadExistingFile()
    {
        $namespace = 'Foo\\' . NamingStrategyInterface::SEPARATOR;
        $className = 'Bar' . uniqid();
        $fileName  = sys_get_temp_dir() . '/foo_' . uniqid() . '.php';

        file_put_contents($fileName, '<?php namespace ' . $namespace . '; class ' . $className . '{}');
        $this
            ->fileLocator
            ->expects($this->once())
            ->method('getProxyFileName')
            ->will($this->returnValue($fileName));

        $this->assertTrue($this->autoloader->__invoke($namespace . '\\' . $className));
        $this->assertTrue(class_exists($namespace . '\\' . $className, false));
    }
}
