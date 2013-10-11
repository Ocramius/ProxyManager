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
use ProxyManager\ProxyGenerator\Util\ReflectionTools;
use ProxyManager\Proxy\Exception\OverloadingObjectException;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Zend\Code\Reflection\MethodReflection;
use ReflectionFunction;

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
     * 
     * @param \Zend\Code\Generator\PropertyGenerator $prototypes
     * @param \ReflectionMethod[]                    $methods        Default methods object
     * @param array                                  $defaultMethods Default user methods added
     */
    public function __construct(PropertyGenerator $prototypes, array $methods, array $defaultMethods = array())
    {
        parent::__construct('__construct');
        
        $list = array();
        $reflectionTools = new ReflectionTools();
        
        foreach($methods as $method) {
            $methodName = $method->getName();
            $className  = $method->getDeclaringClass()->getName();
            
            $methodReflection   = new MethodReflection($className, $methodName);
            $reflection         = ZendMethodGenerator::fromReflection($methodReflection);
            $argReflection      = $reflectionTools->getArgumentsLine($methodReflection);
            
            $list[$methodName][$argReflection->toIdentifiableString()] = 'function(' . $argReflection->toString() . ') {' . trim($reflection->getBody()) . '};';
        }
        
        foreach($defaultMethods as $methodName => $defaultMethod) {
            
            $closures = is_array($defaultMethod) ? $defaultMethod : array($defaultMethod);
            foreach($closures as $closure) {
                $argReflection      = $reflectionTools->getArgumentsLine(new ReflectionFunction($closure));

                if (isset($list[$methodName][$argReflection->toIdentifiableString()])) {
                    throw new OverloadingObjectException(sprintf('A method "%s" with the same prototype already exists', $methodName));
                }

                $content = ReflectionTools::getFunctionContent($closure);
                $list[$methodName][$argReflection->toIdentifiableString()] = 'function(' . $argReflection->toString() . ') {' . trim($content) . '};';
            }
        }
        
        $body = '';
        foreach($list as $methodName => $method) {
            foreach($method as $identifiableString => $closure) {
                $body .= '$closure = ' . $closure . "\n"
                       . '$this->' . $prototypes->getName() . "['$methodName'][" . var_export($identifiableString, true) . "] = \$closure;\n";
            }
        }
        
        $this->setBody($body);
    }
}
