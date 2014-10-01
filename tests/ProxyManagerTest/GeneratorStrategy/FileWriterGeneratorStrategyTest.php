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
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;

/**
 * Tests for {@see \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
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
}
