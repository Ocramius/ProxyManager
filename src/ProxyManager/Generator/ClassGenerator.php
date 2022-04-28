<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\ClassGenerator as LaminasClassGenerator;
use ReflectionParameter;

use function method_exists;

/**
 * Class generator that ensures that interfaces/classes that are implemented/extended are FQCNs
 *
 * @internal do not use this in your code: it is only here for internal use
 */
class ClassGenerator extends LaminasClassGenerator
{
    public function generate(): string
    {
        $extendedClass = $this->getExtendedClass();

        foreach ($this->getMethods() as $method) {
            $class = $extendedClass;

            if ($class === null) {
                foreach ($this->getImplementedInterfaces() as $interface) {
                    if (method_exists($interface, $method->getName())) {
                        $class = $interface;
                        break;
                    }
                }
            }

            if ($class === null || ! method_exists($class, $method->getName())) {
                continue;
            }

            foreach ($method->getParameters() as $parameter) {
                $default = $parameter->getDefaultValue();

                if ($default === null) {
                    continue;
                }

                $parameter->setDefaultValue(new ValueGenerator($default, new ReflectionParameter([$class, $method->getName()], $parameter->getName())));
            }
        }

        return parent::generate();
    }
}
