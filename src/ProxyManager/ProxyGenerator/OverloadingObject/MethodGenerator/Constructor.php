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

use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Implementation for overloading objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class Constructor extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(PropertyGenerator $prototypes, array $methods)
    {
        parent::__construct('__construct');
        
        $body = '';
        /* @var $methods \ReflectionMethod[] */
        foreach($methods as $method) {
            $methodName = $method->getName();
            
            $methodReflection = new MethodReflection(
                $method->getDeclaringClass()->getName(),
                $method->getName()
            );
            $reflection = ZendMethodGenerator::fromReflection($methodReflection);
            
            $list = array();
            foreach ($method->getParameters() as $parameter) {
                $list[] = '$' . $parameter->getName();
            }
            
            $content = $reflection->getBody();
            $body .= '$closure = function(' . implode(',', $list) . ') {' . trim($content) . '};' . "\n"
                   . '$prototype = $this->getPrototypeFromClosure($closure);' . "\n"
                   . '$this->' . $prototypes->getName() . "['$methodName'][\$prototype] = \$closure;\n";
        }
        
        $this->setBody($body);
    }
}
