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

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator
 * @group Coverage
 */
class PublicScopeSimulatorTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleGet()
    {
        $code = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_GET,
            'foo',
            null,
            null,
            'bar'
        );

        $this->assertStringMatchesFormat('%a{%areturn $%s->$foo;%a}%a$bar = %s;', $code);
    }

    public function testSimpleSet()
    {
        $code = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_SET,
            'foo',
            'baz',
            null,
            'bar'
        );

        $this->assertStringMatchesFormat('%a{%areturn $%s->$foo = $baz;%a}%a$bar = %s;', $code);
    }

    public function testSimpleIsset()
    {
        $code = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_ISSET,
            'foo',
            null,
            null,
            'bar'
        );

        $this->assertStringMatchesFormat('%a{%areturn isset($%s->$foo);%a}%a$bar = %s;', $code);
    }

    public function testSimpleUnset()
    {
        $code = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_UNSET,
            'foo',
            null,
            null,
            'bar'
        );

        $this->assertStringMatchesFormat('%a{%aunset($%s->$foo);%a}%a$bar = %s;', $code);
    }

    public function testSetRequiresValueParameterName()
    {
        $this->setExpectedException('InvalidArgumentException');

        PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_SET,
            'foo',
            null,
            null,
            'bar'
        );
    }

    public function testDelegatesToValueHolderWhenAvailable()
    {
        $code = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_SET,
            'foo',
            'baz',
            new PropertyGenerator('valueHolder'),
            'bar'
        );

        $this->assertStringMatchesFormat(
            '%A$targetObject = $this->valueHolder;%a{%areturn $%s->$foo = $baz;%a}%a$bar = %s;',
            $code
        );
    }

    public function testSetRequiresValidOperation()
    {
        $this->setExpectedException('InvalidArgumentException');

        PublicScopeSimulator::getPublicAccessSimulationCode('invalid', 'foo');
    }

    public function testWillReturnDirectlyWithNoReturnParam()
    {
        $code = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_GET,
            'foo'
        );

        $this->assertStringMatchesFormat('%a{%areturn $%s->$foo;%a}%areturn %s;', $code);
    }
}
