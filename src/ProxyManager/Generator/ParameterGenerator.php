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
     * {@inheritDoc}
     */
    protected static $simple = array(
        'int',
        'bool',
        'string',
        'float',
        'resource',
        'mixed',
        'object',
        'array',
        'callable'
    );

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

        if ($reflectionParameter->isArray()) {
            $param->setType('array');
        } else {
            $typeClass = $reflectionParameter->getClass();
            if ($typeClass) {
                $param->setType($typeClass->getName());
            }
        }

        $param->setPosition($reflectionParameter->getPosition());

        if ($reflectionParameter->isOptional()) {
            $param->setDefaultValue($reflectionParameter->getDefaultValue());
        }
        $param->setPassedByReference($reflectionParameter->isPassedByReference());

        return $param;
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {
        if ($type && ! in_array($type, static::$simple)) {
            $type = '\\' . trim($type, '\\');
        }

        return parent::setType($type);
    }

    /**
     * @override fixes type hinting for arrays
     *
     * {@inheritDoc}
     */
    public function generate()
    {
        $output = '';

        if ($this->type) {
            $output .= $this->type . ' ';
        }

        if (true === $this->passedByReference) {
            $output .= '&';
        }

        $output .= '$' . $this->name;

        if ($this->defaultValue !== null) {
            $output .= ' = ';
            if (is_string($this->defaultValue)) {
                $output .= ValueGenerator::escape($this->defaultValue);
            } elseif ($this->defaultValue instanceof ValueGenerator) {
                $this->defaultValue->setOutputMode(ValueGenerator::OUTPUT_SINGLE_LINE);
                $output .= $this->defaultValue;
            } else {
                $output .= $this->defaultValue;
            }
        }

        return $output;
    }
}
