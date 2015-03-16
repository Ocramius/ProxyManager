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

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_TestCase;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Configuration;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;

class AccessInterceptorScopeLocalizerAllowUseVariadicNotationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.6.0', '<=')) {
            $this->markTestSkipped('Teste can\'t run on < 5.6.0 php version');
        }
    }

    public function testCanCreateAndRegisterCallbackWithVariadicNotation()
    {
        $configuration = new Configuration();
        $factory = new AccessInterceptorScopeLocalizerFactory($configuration);

        $targetObject = new ClassWithMethodWithVariadicFunction();

        $object = $factory->createProxy($targetObject, [ function ($paratemers) {
            return 'Foo Baz';
        },]);

        $this->assertNull($object->bar);
        $this->assertNull($object->baz);

        $object->foo('Ocramius', 'Malukenho', 'Danizord');
        $this->assertSame('Ocramius', $object->bar);
        $this->assertSame(
            [
                [
                    'Malukenho',
                    'Danizord',
                ],
            ],
            $object->baz
        );
    }
}
