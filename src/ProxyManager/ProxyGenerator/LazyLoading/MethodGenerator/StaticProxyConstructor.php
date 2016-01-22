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

namespace ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The `staticProxyConstructor` implementation for lazy loading proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Static constructor
     *
     * @param PropertyGenerator $initializerProperty
     * @param Properties        $properties
     */
    public function __construct(PropertyGenerator $initializerProperty, Properties $properties)
    {
        parent::__construct('staticProxyConstructor', [], static::FLAG_PUBLIC | static::FLAG_STATIC);

        $this->setParameter(new ParameterGenerator('initializer'));

        $this->setDocblock("Constructor for lazy initialization\n\n@param \\Closure|null \$initializer");
        $this->setBody(
            'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();' . "\n\n"
            . UnsetPropertiesGenerator::generateSnippet($properties, 'instance')
            . '$instance->' . $initializerProperty->getName() . ' = $initializer;' . "\n\n"
            . 'return $instance;'
        );
    }
}
