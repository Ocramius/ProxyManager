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

namespace ProxyManager\ProxyGenerator\Util;

/**
 * Generates code necessary to unset all the given properties from a particular given instance string name
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
final class UnsetPropertiesGenerator
{
    private static $closureTemplate = <<<'PHP'
\Closure::bind(function (\%s $instance) {
    %s
}, $%s, %s)->__invoke($%s);
PHP;

    public static function generateSnippet(Properties $properties, string $instanceName) : string
    {
        return self::generateUnsetAccessiblePropertiesCode($properties, $instanceName)
            . self::generateUnsetPrivatePropertiesCode($properties, $instanceName);
    }

    private static function generateUnsetAccessiblePropertiesCode(Properties $properties, string $instanceName) : string
    {
        $accessibleProperties = $properties->getAccessibleProperties();

        if (! $accessibleProperties) {
            return '';
        }

        return  self::generateUnsetStatement($accessibleProperties, $instanceName) . "\n\n";
    }

    private static function generateUnsetPrivatePropertiesCode(Properties $properties, string $instanceName) : string
    {
        $groups = $properties->getGroupedPrivateProperties();

        if (! $groups) {
            return '';
        }

        $unsetClosureCalls = [];

        /* @var $privateProperties \ReflectionProperty[] */
        foreach ($groups as $privateProperties) {
            /* @var $firstProperty \ReflectionProperty */
            $firstProperty  = reset($privateProperties);

            $unsetClosureCalls[] = self::generateUnsetClassPrivatePropertiesBlock(
                $firstProperty->getDeclaringClass(),
                $privateProperties,
                $instanceName
            );
        }

        return implode("\n\n", $unsetClosureCalls) . "\n\n";
    }

    private static function generateUnsetClassPrivatePropertiesBlock(
        \ReflectionClass $declaringClass,
        array $properties,
        string $instanceName
    ) : string {
        $declaringClassName = $declaringClass->getName();

        return sprintf(
            self::$closureTemplate,
            $declaringClassName,
            self::generateUnsetStatement($properties, 'instance'),
            $instanceName,
            var_export($declaringClassName, true),
            $instanceName
        );
    }

    private static function generateUnsetStatement(array $properties, string $instanceName) : string
    {
        return 'unset('
            . implode(
                ', ',
                array_map(
                    function (\ReflectionProperty $property) use ($instanceName) : string {
                        return '$' . $instanceName . '->' . $property->getName();
                    },
                    $properties
                )
            )
            . ');';
    }
}
