<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\NullObject\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\IdentifierSuffixer;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method decorator for null objects
 */
class NullObjectMethodInterceptor extends MethodGenerator
{
    /**
     * @return self|static
     */
    public static function generateMethod(MethodReflection $originalMethod) : self
    {
        /** @var self $method */
        $method = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);

        if ($originalMethod->returnsReference()) {
            $reference = IdentifierSuffixer::getIdentifier('ref');

            $method->setBody("\$reference = null;\nreturn \$" . $reference . ';');
        }

        return $method;
    }
}
