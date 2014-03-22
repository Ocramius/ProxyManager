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

namespace ProxyManager\Generator;

use ReflectionException;
use Zend\Code\Generator\ParameterGenerator as ZendParameterGenerator;
use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Reflection\ParameterReflection;

/**
 * Parameter generator that ensures that the parameter type is a FQCN when it is a class
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ParameterGenerator extends ZendParameterGenerator
{
    /**
     * @override - uses `static` to instantiate the parameter
     *
     * {@inheritDoc}
     */
    public static function fromReflection(ParameterReflection $reflectionParameter)
    {
        /* @var $param self */
        $param = new static();

        $param->setName($reflectionParameter->getName());
        $param->setPosition($reflectionParameter->getPosition());

        $type = self::extractParameterType($reflectionParameter);

        if (null !== $type) {
            $param->setType($type);
        }

        self::setOptionalParameter($param, $reflectionParameter);

        $param->setPassedByReference($reflectionParameter->isPassedByReference());

        return $param;
    }

    /**
     * Retrieves the type of a reflection parameter (null if none is found)
     *
     * @param ParameterReflection $reflectionParameter
     *
     * @return string|null
     */
    private static function extractParameterType(ParameterReflection $reflectionParameter)
    {
        if ($reflectionParameter->isArray()) {
            return 'array';
        }

        if (method_exists($reflectionParameter, 'isCallable') && $reflectionParameter->isCallable()) {
            return 'callable';
        }

        if ($typeClass = $reflectionParameter->getClass()) {
            return $typeClass->getName();
        }

        return null;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return $this->getGeneratedType()
            . (true === $this->passedByReference ? '&' : '')
            . '$' . $this->name
            . $this->generateDefaultValue();
    }

    /**
     * @return string
     */
    private function generateDefaultValue()
    {
        if (null === $this->defaultValue) {
            return '';
        }

        $defaultValue = $this->defaultValue instanceof ValueGenerator
            ? $this->defaultValue
            : new ValueGenerator($this->defaultValue);

        $defaultValue->setOutputMode(ValueGenerator::OUTPUT_SINGLE_LINE);

        return ' = ' . $defaultValue;
    }

    /**
     * Retrieves the generated parameter type
     *
     * @return string
     */
    private function getGeneratedType()
    {
        if (! $this->type || in_array($this->type, static::$simple)) {
            return '';
        }

        if ('array' === $this->type || 'callable' === $this->type) {
            return $this->type . ' ';
        }

        return '\\' . trim($this->type, '\\') . ' ';
    }

    /**
     * Set the default value for a parameter (if it is optional)
     *
     * @param ZendParameterGenerator $parameterGenerator
     * @param ParameterReflection    $reflectionParameter
     */
    private static function setOptionalParameter(
        ZendParameterGenerator $parameterGenerator,
        ParameterReflection $reflectionParameter
    ) {
        if ($reflectionParameter->isOptional()) {
            try {
                $parameterGenerator->setDefaultValue($reflectionParameter->getDefaultValue());
            } catch (ReflectionException $e) {
                $parameterGenerator->setDefaultValue(null);
            }
        }
    }
}
