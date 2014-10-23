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
use ProxyManager\ProxyGenerator\OverloadingObject\PropertyGenerator\PrototypesProperty;
use ProxyManager\ProxyGenerator\Util\ReflectionTools;
use ProxyManager\ProxyGenerator\Util\ReflectionTools\MethodArgumentsParsing;
use ProxyManager\ProxyGenerator\Util\ReflectionTools\FunctionArgumentsParsing;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Reflection\MethodReflection;
use ReflectionFunction;

/**
 * Implementation for overloading objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class OverloadingObjectMethodInterceptor extends MethodGenerator
{
    /**
     * Property name
     * @var string
     */
    public static $prototypeName;
    
    /**
     * 
     * @param PrototypesProperty                     $prototypes
     * @param \Zend\Code\Reflection\MethodReflection $originalMethod
     * @param array                                  $defaultMethods
     * @return OverloadingObjectMethodInterceptor|static
     * @throws OverloadingObjectException
     */
    public static function generateMethod(PrototypesProperty $prototypes, MethodReflection $originalMethod, array $defaultMethods = array())
    {
        /* @var $method self */
        $method = static::fromReflection($originalMethod);
        
        foreach($method->getParameters() as $parameter) {
            $parameter->setDefaultValue(null);
        }
        
        $argLine       = MethodArgumentsParsing::toIdentifiableString($originalMethod);
        $prototypeName = self::getPrototypeName();
        $list          = array($argLine);
        
        $body =  '$self = $this;' . "\n"
            . '$args = func_get_args();' . "\n"
            . $prototypeName . ' = \ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing::toIdentifiableString($args);' . "\n"
            . 'if (' . $prototypeName . ' == "' . $argLine . '") {' . "\n"
            .      $method->getBody() . "\n"
            . '}' . "\n";
        
        foreach($defaultMethods as $methodName => $defaultMethod) {
            
            $closures = is_array($defaultMethod) ? $defaultMethod : array($defaultMethod);
            foreach($closures as $closure) {
                $reflectionFunction = new ReflectionFunction($closure);
                $argLine = FunctionArgumentsParsing::toIdentifiableString($reflectionFunction);

                if (in_array($argLine, $list)) {
                    throw new OverloadingObjectException(sprintf('A method "%s" with the same prototype already exists', $methodName));
                }

                $list[] = $argLine;
                $content = ReflectionTools::getFunctionContent($closure);
                $body .= 'else if (' . $prototypeName . ' === ' . var_export($argLine, true) . ') {' . "\n";
                      
                foreach($reflectionFunction->getParameters() as $functionParameter) {
                    if ($functionParameter->isPassedByReference()) {
                        $body .= '$trace = debug_backtrace();' . "\n"
                               . '$' . $functionParameter->getName() . ' = ' . '& $trace[0]["args"][' . $functionParameter->getPosition() . '];' . "\n";
                    } else {
                        $body .= '$' . $functionParameter->getName() . ' = ' . '$args[' . $functionParameter->getPosition() . '];' . "\n";
                    }
                }
                
                $body .=  $content . "\n"
                        . '}' . "\n";
            }
        }
        
        $body .= 'else if (isset($this->' . $prototypes->getName() . '["' . $method->getName() . '"][' . $prototypeName . '])) {' . "\n"
               . '    return call_user_func_array($this->' . $prototypes->getName() . '["' . $method->getName() . '"][' . $prototypeName . '], $args);' . "\n"
               . '} else {' . "\n"
               . '    trigger_error("Call to undefined method ' . $method->getName() . '", E_USER_ERROR);' . "\n"
               . '}';
        $method->setBody($body);

        return $method;
    }
    
    /**
     * 
     * @param PrototypesProperty $prototypes
     * @param string             $name
     * @param array              $functions
     * @return OverloadingObjectMethodInterceptor|static
     * @throws OverloadingObjectException
     */
    public static function generateFunction(PrototypesProperty $prototypes, $name, array $functions)
    {
        $method = new static();
        $method->setParameters(array());
        $method->setName($name);
        
        $prototypeName = self::getPrototypeName();
        $list          = array();
        
        $body = '$self = $this;' . "\n"
            . '$args = func_get_args();' . "\n"
            . $prototypeName . ' = \ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing::toIdentifiableString($args);' . "\n";
        
        foreach($functions as $function) {
            $reflectionFunction = new ReflectionFunction($function);
            $argLine = FunctionArgumentsParsing::toIdentifiableString($reflectionFunction);

            if (in_array($argLine, $list)) {
                throw new OverloadingObjectException(sprintf('A method "%s" with the same prototype already exists', $name));
            }
            
            $list[] = $argLine;
            $content = ReflectionTools::getFunctionContent($function);
            $body .= 'if (' . $prototypeName . ' === ' . var_export($argLine, true) . ') {' . "\n";
            
            foreach($reflectionFunction->getParameters() as $functionParameter) {
                if ($functionParameter->isPassedByReference()) {
                    $body .= '$trace = debug_backtrace();' . "\n"
                           . '$' . $functionParameter->getName() . ' = ' . '& $trace[0]["args"][' . $functionParameter->getPosition() . '];' . "\n";
                } else {
                    $body .= '$' . $functionParameter->getName() . ' = ' . '$args[' . $functionParameter->getPosition() . '];' . "\n";
                }
            }
            
            $body .=  $content . "\n"
                    . '}' . "\n";
        }
        
        $body .= 'else if (isset($this->' . $prototypes->getName() . '["' . $method->getName() . '"][' . $prototypeName . '])) {' . "\n"
               . '    return call_user_func_array($this->' . $prototypes->getName() . '["' . $method->getName() . '"][' . $prototypeName . '], $args);' . "\n"
               . '} else {' . "\n"
               . '    trigger_error("Call to undefined method ' . $method->getName() . '", E_USER_ERROR);' . "\n"
               . '}';
        $method->setBody($body);
        
        return $method;
    }
    
    /**
     * Get property name to avoid colision
     * @return string
     */
    public static function getPrototypeName()
    {
        if (null === self::$prototypeName) {
            self::$prototypeName = '$' . UniqueIdentifierGenerator::getIdentifier('prototype');
        }
        return self::$prototypeName;
    }
}
