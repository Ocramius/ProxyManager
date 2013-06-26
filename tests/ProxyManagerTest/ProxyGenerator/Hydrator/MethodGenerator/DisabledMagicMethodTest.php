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

namespace ProxyManagerTest\ProxyGenerator\Hydrator\MethodGenerator;

use ReflectionClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\DisabledMagicMethod;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\DisabledMagicMethod}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\DisabledMagicMethod
 */
class DisabledMagicMethodTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\DisabledMagicMethod::generate
     */
    public function testGeneratedStructure()
    {
        $disabledMethod  = new DisabledMagicMethod(new ReflectionClass($this), __FUNCTION__, array('foo'));

        $this->assertStringMatchesFormat(
            '%athrow \\ProxyManager\\Exception\\DisabledMethodException::disabledMethod(__METHOD__);%a',
            $disabledMethod->generate()
        );
        $this->assertSame(__FUNCTION__, $disabledMethod->getName());
        $this->assertCount(1, $disabledMethod->getParameters());
    }
}
