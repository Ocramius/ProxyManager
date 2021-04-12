<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionClass;

use function implode;
use function sprintf;

use const PHP_EOL;

/**
 * The `bindProxyProperties` method implementation for access interceptor scope localizers
 */
class BindProxyProperties extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct(
            'bindProxyProperties',
            [
                new ParameterGenerator('localizedObject', $originalClass->getName()),
                new ParameterGenerator('prefixInterceptors', 'array', []),
                new ParameterGenerator('suffixInterceptors', 'array', []),
            ],
            self::FLAG_PRIVATE,
            null,
            "@override constructor to setup interceptors\n\n"
            . '@param \\' . $originalClass->getName() . " \$localizedObject\n"
            . "@param \\Closure[] \$prefixInterceptors method interceptors to be used before method logic\n"
            . '@param \\Closure[] $suffixInterceptors method interceptors to be used before method logic'
        );

        $properties = Properties::fromReflectionClass($originalClass);

        $bodyLines = ['$class = new \ReflectionObject($localizedObject);'];
        foreach ($properties->getInstanceProperties() as $property) {
            $bodyLines[] = sprintf(
                '$this->bindProxyProperty($localizedObject, $class, \'%s\');',
                $property->getName()
            );
        }

        $bodyLines[] = sprintf('$this->%s = $prefixInterceptors;', $prefixInterceptors->getName());
        $bodyLines[] = sprintf('$this->%s = $suffixInterceptors;', $suffixInterceptors->getName());

        $this->setBody(implode(PHP_EOL, $bodyLines));
    }
}
