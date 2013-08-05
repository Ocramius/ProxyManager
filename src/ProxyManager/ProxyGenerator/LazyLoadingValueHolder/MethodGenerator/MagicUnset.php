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

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__unset` method for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicUnset extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct($originalClass, '__unset', array(new ParameterGenerator('name')));

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $callParent  = '';

        $this->setDocblock(($originalClass->hasMethod('__isset') ? "{@inheritDoc}\n" : '') . '@param string $name');

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    unset($this->' . $valueHolder . '->$name);' . "\n\n    return;"
                . "\n}\n\n";
        }

        $callParent .= PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_UNSET,
            'name',
            null,
            $valueHolderProperty
        );

        $this->setBody(
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolder . ', $this, \'__unset\', array(\'name\' => $name), $this->'
            . $initializer . ');' . "\n\n" . $callParent
        );
    }
}
