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

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptor\MethodGenerator;

use ReflectionClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicWakeupTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup::__construct
     */
    public function testBodyStructure()
    {
        $reflection  = new ReflectionClass(
            'ProxyManagerTestAsset\\ProxyGenerator\\LazyLoading\\MethodGenerator\\ClassWithTwoPublicProperties'
        );

        $magicWakeup = new MagicWakeup($reflection);

        $this->assertSame('__wakeup', $magicWakeup->getName());
        $this->assertCount(0, $magicWakeup->getParameters());
        $this->assertSame("unset(\$this->bar, \$this->baz);", $magicWakeup->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup::__construct
     */
    public function testBodyStructureWithoutPublicProperties()
    {
        $magicWakeup = new MagicWakeup(new ReflectionClass('ProxyManagerTestAsset\\EmptyClass'));

        $this->assertSame('__wakeup', $magicWakeup->getName());
        $this->assertCount(0, $magicWakeup->getParameters());
        $this->assertEmpty($magicWakeup->getBody());
    }
}
