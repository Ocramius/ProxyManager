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

namespace ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The `staticProxyConstructor` implementation for access interceptor value holders
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param ReflectionClass   $originalClass
     * @param PropertyGenerator $valueHolder
     * @param PropertyGenerator $prefixInterceptors
     * @param PropertyGenerator $suffixInterceptors
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $valueHolder,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct('staticProxyConstructor', [], static::FLAG_PUBLIC | static::FLAG_STATIC);

        $prefix = new ParameterGenerator('prefixInterceptors');
        $suffix = new ParameterGenerator('suffixInterceptors');

        $prefix->setDefaultValue([]);
        $suffix->setDefaultValue([]);
        $prefix->setType('array');
        $suffix->setType('array');

        $this->setParameter(new ParameterGenerator('wrappedObject'));
        $this->setParameter($prefix);
        $this->setParameter($suffix);
        $this->setReturnType($originalClass->getName());

        $this->setDocblock(
            "Constructor to setup interceptors\n\n"
            . "@param \\" . $originalClass->getName() . " \$wrappedObject\n"
            . "@param \\Closure[] \$prefixInterceptors method interceptors to be used before method logic\n"
            . "@param \\Closure[] \$suffixInterceptors method interceptors to be used before method logic\n\n"
            . "@return self"
        );

        $this->setBody(
            'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();' . "\n\n"
            . UnsetPropertiesGenerator::generateSnippet(Properties::fromReflectionClass($originalClass), 'instance')
            . '$instance->' . $valueHolder->getName() . " = \$wrappedObject;\n"
            . '$instance->' . $prefixInterceptors->getName() . " = \$prefixInterceptors;\n"
            . '$instance->' . $suffixInterceptors->getName() . " = \$suffixInterceptors;\n\n"
            . 'return $instance;'
        );
    }
}
