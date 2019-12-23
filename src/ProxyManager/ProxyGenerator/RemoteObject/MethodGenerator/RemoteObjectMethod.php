<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

use function str_replace;
use function var_export;

/**
 * Method decorator for remote objects
 */
class RemoteObjectMethod extends MethodGenerator
{
    /** @var string */
    private const TEMPLATE
        = <<<PHP
\$reflectionMethod = new \\ReflectionMethod(__CLASS__, __FUNCTION__);
\$args = \\func_get_args();
foreach (\\array_slice(\$reflectionMethod->getParameters(), \\count(\$args)) as \$param) {
            /**
             * @var ReflectionParameter \$param
             */
            if (\$param->isDefaultValueAvailable()) {
                \$args[] = \$param->getDefaultValue();
            }
}

#proxiedReturn#
PHP;

    /** @return self|static */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $adapterProperty,
        ReflectionClass $originalClass
    ): self {
        /** @var self $method */
        $method = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $proxiedReturn = '$return = $this->' . $adapterProperty->getName()
            . '->call(' . var_export($originalClass->getName(), true)
            . ', ' . var_export($originalMethod->getName(), true) . ', $args);' . "\n\n"
            . ProxiedMethodReturnExpression::generate('$return', $originalMethod);

        $body = str_replace(
            ['#proxiedReturn#'],
            ['#proxciedReturn#' => $proxiedReturn],
            self::TEMPLATE,
        );

        $method->setBody(
            $body
        );

        return $method;
    }
}
