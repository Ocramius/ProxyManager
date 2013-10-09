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

namespace ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;
use ProxyManager\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__call` for overloading objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class MagicCall extends MagicMethodGenerator
{
    /**
     * @param \ReflectionClass                          $originalClass
     * @param \Zend\Code\Generator\PropertyGenerator    $overloading
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prototypes
    ) {
        parent::__construct($originalClass, '__call', array(
            new ParameterGenerator('name'), new ParameterGenerator('arguments', 'array'))
        );

        $override    = $originalClass->hasMethod('__call');

        $this->setDocblock(($override ? "{@inheritDoc}\n" : '') . "@param string \$name\n@param array \$arguments");

        $body = 
              '$argReflection = new \ProxyManager\ProxyGenerator\Util\ReflectionTools();'
            . '$prototype = $argReflection->getArgumentsLine($arguments)->toIdentifiableString();' . "\n"
            . 'if (isset($this->' . $prototypes->getName() . '[$name][$prototype])) {' . "\n"
            . '    return call_user_func_array($this->' . $prototypes->getName() . '[$name][$prototype], $arguments);' . "\n"
            . '}';
        $this->setBody($body);
    }
}
