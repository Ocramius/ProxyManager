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
use ProxyManager\ProxyGenerator\Util\Properties;
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
            . $this->generateUnsetPropertiesCode($properties)
            . '$instance->' . $initializerProperty->getName() . ' = $initializer;' . "\n\n"
            . 'return $instance;'
        );
    }

    /**
     * @param Properties $properties
     *
     * @return string
     */
    private function generateUnsetPropertiesCode(Properties $properties)
    {
        $code = '';

        if ($accessibleProperties = $properties->getAccessibleProperties()) {
            $code .= $this->getUnsetPropertiesGroupCode($accessibleProperties) . "\n";
        }

        foreach ($this->getGroupedPrivateProperties($properties) as $className => $privateProperties) {
            $code .= "\\Closure::bind(function (\$instance) {\n"
                . '    ' . $this->getUnsetPropertiesGroupCode($privateProperties)
                . '}, null, ' . var_export($className, true) . ")->__invoke(\$instance);\n\n";
        }

        return $code;
    }

    /**
     * @param Properties $properties
     *
     * @return ReflectionProperty[][] indexed by class name and property name
     */
    private function getGroupedPrivateProperties(Properties $properties)
    {
        $propertiesMap = [];

        foreach ($properties->getPrivateProperties() as $property) {
            $class = & $propertiesMap[$property->getDeclaringClass()->getName()];

            $class[$property->getName()] = $property;
        }

        return $propertiesMap;
    }

    /**
     * @param ReflectionProperty[] $properties
     *
     * @return string
     */
    private function getUnsetPropertiesGroupCode(array $properties)
    {
        return 'unset('
            . implode(
                ', ',
                array_map(
                    function (ReflectionProperty $property) {
                        return '$instance->' . $property->getName();
                    },
                    $properties
                )
            )
            . ");\n";
    }
}
