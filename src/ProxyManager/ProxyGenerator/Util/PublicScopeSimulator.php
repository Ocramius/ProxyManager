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

namespace ProxyManager\ProxyGenerator\Util;

use Zend\Code\Generator\PropertyGenerator;

/**
 * Generates code necessary to simulate a fatal error in case of unauthorized
 * access to class members in magic methods even when in child classes and dealing
 * with protected members.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class PublicScopeSimulator
{
    const OPERATION_SET   = 'set';
    const OPERATION_GET   = 'get';
    const OPERATION_ISSET = 'isset';
    const OPERATION_UNSET = 'unset';

    /**
     * @param string            $operationType      operation to execute: one of 'get', 'set', 'isset' or 'unset'
     * @param string            $nameParameter      name of the `name` parameter of the magic method
     * @param string|null       $valueParameter     name of the `value` parameter of the magic method
     * @param PropertyGenerator $valueHolder        name of the property containing the target object from which
     *                                              to read the property. `$this` if none provided
     * @param string|null       $returnPropertyName name of the property to which we want to assign the result of
     *                                              the operation. Return directly if none provided
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function getPublicAccessSimulationCode(
        $operationType,
        $nameParameter,
        $valueParameter = null,
        PropertyGenerator $valueHolder = null,
        $returnPropertyName = null
    ) {
        $byRef = self::getByRefReturnValue($operationType);
        $value = static::OPERATION_SET === $operationType ? ', $value' : '';

        return '$targetObject = ' . self::getTargetObject($valueHolder) . ";\n"
            . '    $accessor = function ' . $byRef . '() use ($targetObject, $name' . $value . ') {' . "\n"
            . '        ' . self::getOperation($operationType, $nameParameter, $valueParameter) . ';' . "\n"
            . "    };\n"
            . self::getScopeReBind()
            . '    ' . ($returnPropertyName ? '$' . $returnPropertyName . ' =' : 'return') . ' $accessor();';
    }

    /**
     * Defines whether the given operation produces a reference.
     *
     * Note: if the object is a wrapper, the wrapped instance is accessed directly. If the object
     * is a ghost or the proxy has no wrapper, then an instance of the parent class is created via
     * on-the-fly unserialization
     *
     * @param string $operationType
     *
     * @return string
     */
    private static function getByRefReturnValue($operationType)
    {
        return (static::OPERATION_GET === $operationType || static::OPERATION_SET === $operationType) ? '& ' : '';
    }

    /**
     * Retrieves the logic to fetch the object on which access should be attempted
     *
     * @param PropertyGenerator $valueHolder
     *
     * @return string
     */
    private static function getTargetObject(PropertyGenerator $valueHolder = null)
    {
        if ($valueHolder) {
            return '$this->' . $valueHolder->getName();
        }

        return 'unserialize(sprintf(\'O:%d:"%s":0:{}\', strlen(get_parent_class($this)), get_parent_class($this)));';
    }

    /**
     * @param string      $operationType
     * @param string      $nameParameter
     * @param string|null $valueParameter
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private static function getOperation($operationType, $nameParameter, $valueParameter)
    {
        switch ($operationType) {
            case static::OPERATION_GET:
                return 'return $targetObject->$' . $nameParameter;
            case static::OPERATION_SET:
                if (! $valueParameter) {
                    throw new \InvalidArgumentException('Parameter $valueParameter not provided');
                }

                return 'return $targetObject->$' . $nameParameter . ' = $' . $valueParameter;
            case static::OPERATION_ISSET:
                return 'return isset($targetObject->$' . $nameParameter . ')';
            case static::OPERATION_UNSET:
                return 'unset($targetObject->$' . $nameParameter . ')';
            default:
                throw new \InvalidArgumentException(sprintf('Invalid operation "%s" provided', $operationType));
        }
    }

    /**
     * Generates code to bind operations to the parent scope if supported by the current PHP version
     *
     * @return string
     */
    private static function getScopeReBind()
    {
        if (PHP_VERSION_ID < 50400) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        return '    $backtrace = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT);' . "\n"
            . '    $scopeObject = isset($backtrace[1][\'object\'])'
            . ' ? $backtrace[1][\'object\'] : new \stdClass();' . "\n"
            . '    $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));' . "\n";
    }
}
