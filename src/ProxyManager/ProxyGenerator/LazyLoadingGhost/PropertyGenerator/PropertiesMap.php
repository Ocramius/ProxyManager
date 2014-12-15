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

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Property that contains the initializer for a lazy object
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class PropertiesMap extends PropertyGenerator
{
    const KEY_VISIBILITY    = 'visibility';
    const KEY_DEFAULT_VALUE = 'defaultValue';
    /**
     * Constructor
     */
    public function __construct(\ReflectionClass $originalClass)
    {
        parent::__construct(
            UniqueIdentifierGenerator::getIdentifier('propertiesMap')
        );

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setStatic(true);
        $this->setDocblock(
            '@var array[][] visibility and default value of defined properties, indexed by class name and property name'
        );
        $this->setDefaultValue($this->getMap($originalClass));
    }

    /**
     * @param \ReflectionClass $originalClass
     *
     * @return int[][]|mixed[][]
     */
    private function getMap(\ReflectionClass $originalClass)
    {
        $map = [];

        foreach ($this->getProperties($originalClass) as $property) {
            $class = & $map[$property->getDeclaringClass()->getName()];

            $class[$property->getName()] = [
                'visibility'   => $this->getPropertyVisibility($property),
                'defaultValue' => $this->getPropertyDefaultValue($property),
            ];
        }

        return $map;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return int
     */
    private function getPropertyVisibility(\ReflectionProperty $property)
    {
        if ($property->isPrivate()) {
            return \ReflectionProperty::IS_PRIVATE;
        }

        if ($property->isProtected()) {
            return \ReflectionProperty::IS_PROTECTED;
        }

        return \ReflectionProperty::IS_PUBLIC;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return mixed
     */
    private function getPropertyDefaultValue(\ReflectionProperty $property)
    {
        $propertyName  = $property->getName();
        $defaultValues = $property->getDeclaringClass()->getDefaultProperties();

        if (! isset($defaultValues[$propertyName])) {
            return null;
        }

        return $defaultValues[$propertyName];
    }

    /**
     * @param \ReflectionClass $originalClass
     *
     * @return \ReflectionProperty[]
     */
    private function getProperties(\ReflectionClass $originalClass)
    {
        $class      = $originalClass;
        $properties = [];

        do {
            $properties = array_merge(
                $properties,
                array_values(array_filter(
                    $class->getProperties(),
                    function (\ReflectionProperty $property) use ($class) {
                        return $property->getDeclaringClass()->getName() === $class->getName()
                            && ! $property->isStatic();
                    }
                ))
            );
        } while ($class = $class->getParentClass());

        return $properties;
    }
}
