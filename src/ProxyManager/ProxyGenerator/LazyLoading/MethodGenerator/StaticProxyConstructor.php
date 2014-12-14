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

namespace ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ReflectionClass;
use ReflectionProperty;
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
     * Constructor
     *
     * @param ReflectionClass   $originalClass
     * @param PropertyGenerator $initializerProperty
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $initializerProperty)
    {
        parent::__construct('staticProxyConstructor', [], static::FLAG_PUBLIC | static::FLAG_STATIC);

        $this->setParameter(new ParameterGenerator('initializer'));

        /* @var $publicProperties \ReflectionProperty[] */
        $publicProperties = $originalClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $unsetProperties  = [];

        foreach ($publicProperties as $publicProperty) {
            $unsetProperties[] = '$instance->' . $publicProperty->getName();
        }

        /* @var $allProperties \ReflectionProperty[] */
        $allProperties = [];
        $class         = $originalClass;

        // @todo move this filter to a separate class
        do {
            foreach ($class->getProperties() as $property) {
                $allProperties[$property->getDeclaringClass()->getName() . '#' . $property->getName()] = $property;
            }
        } while ($class = $class->getParentClass());

        $this->setDocblock("Constructor for lazy initialization\n\n@param \\Closure|null \$initializer");
        $this->setBody(
            'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();' . "\n\n"
            . implode("\n", array_map([$this, 'getUnsetPropertyCode'], $allProperties))
            . '$instance->' . $initializerProperty->getName() . ' = $initializer;' . "\n\n"
            . 'return $instance;'
        );
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return string
     */
    private function getUnsetPropertyCode(ReflectionProperty $property)
    {
        if (! $property->isPrivate()) {
            return 'unset($instance->' . $property->getName() . ");\n";
        }
        return "\\Closure::bind(function (\$instance) {\n"
        . '   unset($instance->' . $property->getName() . ");\n"
        . '}, null, ' . var_export($property->getDeclaringClass()->getName(), true) . ")->__invoke(\$instance);\n";
    }
}
