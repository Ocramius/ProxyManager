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

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\LazyLoadingInterface::isProxyInitialized}
 * for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class CallInitializer extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        PropertyGenerator $initializerProperty,
        PropertyGenerator $publicPropsDefaults,
        PropertyGenerator $initTracker
    ) {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('callInitializer'));
        $this->setDocblock("Triggers initialization logic for this ghost object");

        $this->setParameters(array(
            new ParameterGenerator('methodName'),
            new ParameterGenerator('parameters', 'array'),
        ));

        $this->setVisibility(static::VISIBILITY_PRIVATE);

        $initializer    = $initializerProperty->getName();
        $initialization = $initTracker->getName();

        $this->setBody(
            'if ($this->' . $initialization . ' || ! $this->' . $initializer . ') {' . "\n    return;\n}\n\n"
            . "\$this->" . $initialization . " = true;\n\n"
            . "foreach (self::\$" . $publicPropsDefaults->getName() . " as \$key => \$default) {\n"
            . "    \$this->\$key = \$default;\n"
            . "}\n\n"
            . '$this->' . $initializer . '->__invoke'
            . '($this, $methodName, $parameters, $this->' . $initializer . ');' . "\n\n"
            . "\$this->" . $initialization . " = false;"
        );
    }
}
