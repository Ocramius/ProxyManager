<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\Util;

use ReflectionClass;
use ReflectionMethod;
use function array_filter;
use function array_flip;
use function array_key_exists;
use function array_map;
use function strtolower;

/**
 * Utility class used to filter methods that can be proxied
 */
final class ProxiedMethodsFilter
{
    /** @var string[] */
    private static $defaultExcluded = [
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__clone',
        '__sleep',
        '__wakeup',
    ];

    /**
     * @param ReflectionClass $class    reflection class from which methods should be extracted
     * @param string[]        $excluded methods to be ignored
     *
     * @return ReflectionMethod[]
     */
    public static function getProxiedMethods(ReflectionClass $class, ?array $excluded = null) : array
    {
        return self::doFilter($class, $excluded ?? self::$defaultExcluded);
    }

    /**
     * @param ReflectionClass $class    reflection class from which methods should be extracted
     * @param string[]        $excluded methods to be ignored
     *
     * @return ReflectionMethod[]
     */
    public static function getAbstractProxiedMethods(ReflectionClass $class, ?array $excluded = null) : array
    {
        return self::doFilter($class, $excluded ?? self::$defaultExcluded, true);
    }

    /**
     * @param string[] $excluded
     *
     * @return ReflectionMethod[]
     */
    private static function doFilter(ReflectionClass $class, array $excluded, bool $requireAbstract = false) : array
    {
        $ignored = array_flip(array_map('strtolower', $excluded));

        return array_filter(
            $class->getMethods(ReflectionMethod::IS_PUBLIC),
            static function (ReflectionMethod $method) use ($ignored, $requireAbstract) : bool {
                return (! $requireAbstract || $method->isAbstract()) && ! (
                    array_key_exists(strtolower($method->getName()), $ignored)
                    || self::methodCannotBeProxied($method)
                );
            }
        );
    }

    /**
     * Checks whether the method cannot be proxied
     */
    private static function methodCannotBeProxied(ReflectionMethod $method) : bool
    {
        return $method->isConstructor() || $method->isFinal() || $method->isStatic();
    }
}
