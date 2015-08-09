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

namespace ProxyManagerTest\Exception;

use PHPUnit_Framework_TestCase;
use ProxyManager\Exception\FileNotWritableException;

/**
 * Tests for {@see \ProxyManager\Exception\FileNotWritableException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Exception\FileNotWritableException
 * @group Coverage
 */
class FileNotWritableExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testFromInvalidMoveOperation()
    {
        $exception = FileNotWritableException::fromInvalidMoveOperation('/tmp/a', '/tmp/b');

        $this->assertInstanceOf('ProxyManager\\Exception\\FileNotWritableException', $exception);
        $this->assertSame(
            'Could not move file "/tmp/a" to location "/tmp/b": either the source file is not readable,'
            . ' or the destination is not writable',
            $exception->getMessage()
        );
    }

    public function testFromNotWritableLocationWithNonFilePath()
    {
        $exception = FileNotWritableException::fromNonWritableLocation(__DIR__);

        $this->assertInstanceOf('ProxyManager\\Exception\\FileNotWritableException', $exception);
        $this->assertSame(
            'Could not write to path "' . __DIR__ . '": exists and is not a file',
            $exception->getMessage()
        );
    }

    public function testFromNotWritableLocationWithNonWritablePath()
    {
        $path = sys_get_temp_dir() . '/' . uniqid('FileNotWritableExceptionTestNonWritable', true);

        mkdir($path, 0555);

        $exception = FileNotWritableException::fromNonWritableLocation($path . '/foo');

        $this->assertInstanceOf('ProxyManager\\Exception\\FileNotWritableException', $exception);
        $this->assertSame(
            'Could not write to path "' . $path . '/foo": is not writable',
            $exception->getMessage()
        );
    }
}
