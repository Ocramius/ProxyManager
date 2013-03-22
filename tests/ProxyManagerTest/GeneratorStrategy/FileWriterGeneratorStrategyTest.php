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

use CG\Generator\PhpClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;

/**
 * Tests for {@see \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class FileWriterGeneratorStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy::__construct
     * @covers \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy::generate
     */
    public function testGenerate()
    {
        $locator   = $this->getMock('ProxyManager\\FileLocator\\FileLocatorInterface');
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = sys_get_temp_dir() . '/FileWriterGeneratorStrategyTest' . uniqid() . '.php';
        $className = 'Foo\\Bar' .uniqid();

        $locator
            ->expects($this->any())
            ->method('getProxyFileName')
            ->with($className)
            ->will($this->returnValue($tmpFile));

        $class = new PhpClass($className);

        $generator->generate($class);

        $this->assertFalse(class_exists($className, false));
        $this->assertTrue(file_exists($tmpFile));

        require $tmpFile;

        $this->assertTrue(class_exists($className, false));
    }
}
