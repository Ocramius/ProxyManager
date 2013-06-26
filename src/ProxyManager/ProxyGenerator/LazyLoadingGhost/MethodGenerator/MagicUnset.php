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

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ReflectionProperty;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__unset` method for lazy loading ghost objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicUnset extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $initializerProperty)
    {
        parent::__construct($originalClass, '__unset', array(new ParameterGenerator('name')));

        $override         = $originalClass->hasMethod('__unset');
        $initializer      = $initializerProperty->getName();
        $publicProperties = array_map(
            function (ReflectionProperty $publicProperty) {
                return var_export($publicProperty->getName(), true);
            },
            $originalClass->getProperties(ReflectionProperty::IS_PUBLIC)
        );

        $this->setDocblock(($override ? "{@inheritDoc}\n" : '') . '@param string $name');

        // @todo can be skipped when no public properties are available
        $callParent = 'if (in_array($name, array(' . implode(', ', $publicProperties) . '))) {' . "\n"
            . '    unset($this->$name);'
            . "\n}";

        if ($override) {
            // @todo move to private static var to remove overhead!
            $callParent .= "\n\nreturn parent::__unset(\$name);";
        }

        $this->setBody(
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this, \'__unset\', array(\'name\' => $name), $this->' . $initializer . ');'
            . "\n\n" . $callParent
        );
    }
}
