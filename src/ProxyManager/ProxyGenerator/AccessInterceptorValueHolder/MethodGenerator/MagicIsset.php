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

namespace ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__isset` for method interceptor value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicIsset extends MagicMethodGenerator
{
    /**
     * Constructor
     * @param ReflectionClass     $originalClass
     * @param PropertyGenerator   $valueHolder
     * @param PropertyGenerator   $prefixInterceptors
     * @param PropertyGenerator   $suffixInterceptors
     * @param PublicPropertiesMap $publicProperties
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $valueHolder,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct($originalClass, '__isset', [new ParameterGenerator('name')]);

        $override        = $originalClass->hasMethod('__isset');
        $valueHolderName = $valueHolder->getName();

        $this->setDocblock(($override ? "{@inheritDoc}\n" : '') . '@param string $name');

        $callParent = PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_ISSET,
            'name',
            'value',
            $valueHolder,
            'returnValue'
        );

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    $returnValue = isset($this->' . $valueHolderName . '->$name);'
                . "\n} else {\n    $callParent\n}\n\n";
        }

        $this->setBody(
            InterceptorGenerator::createInterceptedMethodBody(
                $callParent,
                $this,
                $valueHolder,
                $prefixInterceptors,
                $suffixInterceptors
            )
        );
    }
}
