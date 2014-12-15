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

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionProperty;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\LazyLoadingInterface::isProxyInitialized}
 * for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class CallInitializer extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param PropertyGenerator $initializerProperty
     * @param PropertyGenerator $initTracker
     * @param Properties        $properties
     */
    public function __construct(
        PropertyGenerator $initializerProperty,
        PropertyGenerator $initTracker,
        Properties $properties
    ) {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('callInitializer'));
        $this->setDocblock("Triggers initialization logic for this ghost object");

        $this->setParameters([
            new ParameterGenerator('methodName'),
            new ParameterGenerator('parameters', 'array'),
        ]);

        $this->setVisibility(static::VISIBILITY_PRIVATE);

        $initializer    = $initializerProperty->getName();
        $initialization = $initTracker->getName();

        $this->setBody(
            'if ($this->' . $initialization . ' || ! $this->' . $initializer . ') {' . "\n    return;\n}\n\n"
            . "\$this->" . $initialization . " = true;\n\n"
            . $this->propertiesInitializationCode($properties)
            . $this->propertiesReferenceArrayCode($properties)
            . '$this->' . $initializer . '->__invoke'
            . '($this, $methodName, $parameters, $this->' . $initializer . ', $properties);' . "\n\n"
            . "\$this->" . $initialization . " = false;"
        );
    }

    /**
     * @param Properties $properties
     *
     * @return string
     */
    private function propertiesInitializationCode(Properties $properties)
    {
        $assignments = [];

        foreach ($properties->getAccessibleProperties() as $property) {
            $assignments[] = '$this->'
                . $property->getName()
                . ' = ' . $this->getExportedPropertyDefaultValue($property)
                . ';';
        }

        foreach ($properties->getPrivateProperties() as $property) {
            $name           = $property->getName();
            $assignments[]  = "\\Closure::bind(function (\$object) {\n"
                . '    $object->' . $name . ' = ' . $this->getExportedPropertyDefaultValue($property) . ";\n"
                . "}, null, " . var_export($property->getDeclaringClass()->getName(), true) . ")->__invoke(\$this)"
                . ';';
        }

        return implode("\n", $assignments) . "\n\n";
    }

    /**
     * @param Properties $properties
     *
     * @return string
     */
    private function propertiesReferenceArrayCode(Properties $properties)
    {
        $assignments = [];

        foreach ($properties->getAccessibleProperties() as $propertyInternalName => $property) {
            $assignments[] = '    '
                . var_export($propertyInternalName, true) . ' => & $this->' . $property->getName()
                . ',';
        }

        foreach ($properties->getPrivateProperties() as $propertyInternalName => $property) {
            $name           = $property->getName();
            $declaringClass = $property->getDeclaringClass()->getName();
            $assignments[]  = '    '
                . var_export($propertyInternalName, true)
                . " => \\Closure::bind(function & (\$object) {\n"
                . '        return $object->' . $name . ";\n"
                . "    }, null, " . var_export($declaringClass, true) . ")->__invoke(\$this)"
                . ',';
        }

        return "\$properties = [\n" . implode("\n", $assignments) . "\n];\n\n";
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return string
     */
    private function getExportedPropertyDefaultValue(ReflectionProperty $property)
    {
        $name     = $property->getName();
        $defaults = $property->getDeclaringClass()->getDefaultProperties();

        return var_export(isset($defaults[$name]) ? $defaults[$name] : null, true);
    }
}
