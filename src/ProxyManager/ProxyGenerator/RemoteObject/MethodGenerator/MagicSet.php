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

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__set` for remote objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class MagicSet extends MagicMethodGenerator
{
    /**
     * Constructor
     * @param ReflectionClass                        $originalClass
     * @param \Zend\Code\Generator\PropertyGenerator $adapterProperty
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $adapterProperty)
    {
        parent::__construct(
            $originalClass,
            '__set',
            [new ParameterGenerator('name'), new ParameterGenerator('value')]
        );

        $this->setDocblock('@param string \$name\n@param mixed \$value');
        $this->setBody(
            '$return = $this->' . $adapterProperty->getName() . '->call(' . var_export($originalClass->getName(), true)
            . ', \'__set\', array($name, $value));' . "\n\n"
            . 'return $return;'
        );
    }
}
