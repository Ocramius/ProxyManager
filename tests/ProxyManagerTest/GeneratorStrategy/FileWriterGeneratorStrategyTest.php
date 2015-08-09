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

namespace ProxyManagerTest\GeneratorStrategy;

use PHPUnit_Framework_TestCase;
use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;

/**
 * Tests for {@see \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 *
 * Note: this test generates temporary files that are not deleted
 */
class FileWriterGeneratorStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy::__construct
     * @covers \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy::generate
     */
    public function testGenerate()
    {
        /* @var $locator \ProxyManager\FileLocator\FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->getMock('ProxyManager\\FileLocator\\FileLocatorInterface');
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = sys_get_temp_dir() . '/' . uniqid('FileWriterGeneratorStrategyTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects($this->any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will($this->returnValue($tmpFile));

        $body = $generator->generate(new ClassGenerator($fqcn));

        $this->assertGreaterThan(0, strpos($body, $className));
        $this->assertFalse(class_exists($fqcn, false));
        $this->assertTrue(file_exists($tmpFile));

        require $tmpFile;

        $this->assertTrue(class_exists($fqcn, false));
    }

    public function testGenerateWillFailIfTmpFileCannotBeWrittenToDisk()
    {
        $tmpDirPath = sys_get_temp_dir() . '/' . uniqid('nonWritable', true);

        mkdir($tmpDirPath, 0555, true);

        /* @var $locator \ProxyManager\FileLocator\FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->getMock('ProxyManager\\FileLocator\\FileLocatorInterface');
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $tmpDirPath . '/' . uniqid('FileWriterGeneratorStrategyFailedFileWriteTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects($this->any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will($this->returnValue($tmpFile));

        $this->setExpectedException('ProxyManager\\Exception\\FileNotWritableException');
        $generator->generate(new ClassGenerator($fqcn));
    }

    public function testGenerateWillFailIfTmpFileCannotBeMovedToFinalDestination()
    {
        /* @var $locator \ProxyManager\FileLocator\FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->getMock('ProxyManager\\FileLocator\\FileLocatorInterface');
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = sys_get_temp_dir() . '/' . uniqid('FileWriterGeneratorStrategyFailedFileMoveTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects($this->any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will($this->returnValue($tmpFile));

        mkdir($tmpFile);

        $this->setExpectedException('ProxyManager\\Exception\\FileNotWritableException');
        $generator->generate(new ClassGenerator($fqcn));
    }

    public function testWhenFailingAllTemporaryFilesAreRemoved()
    {
        $tmpDirPath = sys_get_temp_dir() . '/' . uniqid('noTempFilesLeftBehind', true);

        mkdir($tmpDirPath);

        /* @var $locator \ProxyManager\FileLocator\FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $locator   = $this->getMock('ProxyManager\\FileLocator\\FileLocatorInterface');
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = $tmpDirPath . '/' . uniqid('FileWriterGeneratorStrategyFailedFileMoveTest', true) . '.php';
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;

        $locator
            ->expects($this->any())
            ->method('getProxyFileName')
            ->with($fqcn)
            ->will($this->returnValue($tmpFile));

        mkdir($tmpFile);

        try {
            $generator->generate(new ClassGenerator($fqcn));

            $this->fail('An exception was supposed to be thrown');
        } catch (FileNotWritableException $exception) {
            rmdir($tmpFile);

            $this->assertEquals(array('.', '..'), scandir($tmpDirPath));
        }
    }
}
