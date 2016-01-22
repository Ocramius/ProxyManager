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

namespace ProxyManager\ProxyGenerator\NullObject\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionClass;
use ReflectionProperty;

/**
 * The `staticProxyConstructor` implementation for null object proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param ReflectionClass $originalClass Reflection of the class to proxy
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct('staticProxyConstructor', [], static::FLAG_PUBLIC | static::FLAG_STATIC);

        $nullableProperties = array_map(
            function (ReflectionProperty $publicProperty) : string {
                return '$instance->' . $publicProperty->getName() . ' = null;';
            },
            Properties::fromReflectionClass($originalClass)->getPublicProperties()
        );

        $this->setDocblock("Constructor for null object initialization");
        $this->setBody(
            'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();' . "\n\n"
            . ($nullableProperties ? implode("\n", $nullableProperties) . "\n\n" : '')
            . 'return $instance;'
        );
    }
}
