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

namespace ProxyManager\ProxyGenerator;

use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromClosure;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\GetPrototypeFromArguments;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\MagicCall;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\Overload;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor;
use ProxyManager\ProxyGenerator\OverloadingObject\PropertyGenerator\PrototypesProperty;
use ProxyManager\Proxy\Exception\OverloadingObjectException;
use ProxyManager\Proxy\OverloadingObjectInterface;
use ProxyManager\Generator\ParameterGenerator;
use Zend\Code\Reflection\ParameterReflection;

use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use ProxyManager\Generator\OverloadingObject\ClassGenerator as OverloadingClassGenerator;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Generator\MethodGenerator;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\OverloadingObjectInterface}
 *
 * {@inheritDoc}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class OverloadingObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * @var PrototypesProperty 
     */
    protected $prototypes;
    
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        if ($originalClass->isInterface()) {
            throw new OverloadingObjectException('Interface overloading is not allowed');
        }
        
        $interfaces          = array('ProxyManager\\Proxy\\OverloadingObjectInterface');
        foreach ($originalClass->getInterfaceNames() as $name) {
            $interfaces[] = $name;
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($this->prototypes = new PrototypesProperty());

        $excluded = array(
            '__call'    => true,
            '__get'    => true,
            '__set'    => true,
            '__isset'  => true,
            '__unset'  => true,
            '__clone'  => true,
            '__sleep'  => true,
            '__wakeup' => true,
        );
        
        /* @var $methods \ReflectionMethod[] */
        $methods = array_filter(
            $originalClass->getMethods(ReflectionMethod::IS_PUBLIC),
            function (ReflectionMethod $method) use ($excluded) {
                return ! (
                    $method->isConstructor()
                    || isset($excluded[strtolower($method->getName())])
                    || $method->isFinal()
                    || $method->isStatic()
                );
            }
        );
        
        $classGenerator->addMethodFromGenerator(new Constructor($this->prototypes, $methods));
        $classGenerator->addMethodFromGenerator(new GetPrototypeFromClosure());
        $classGenerator->addMethodFromGenerator(new GetPrototypeFromArguments());
        $classGenerator->addMethodFromGenerator(new MagicCall($originalClass, $this->prototypes));
        $classGenerator->addMethodFromGenerator(new Overload($this->prototypes));
        
        foreach ($methods as $method) {
            $classGenerator->addMethodFromGenerator(
                OverloadingObjectMethodInterceptor::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                )
            );
        }
    }
    
    /**
     * Create proxy documentation
     * 
     * @param OverloadingObjectInterface $proxy
     * @param string                     $className
     * 
     * @return string
     */
    public function generateDocumentation(OverloadingObjectInterface $proxy, $className)
    {
        $reflection = new ReflectionClass($proxy);
        $property = $reflection->getProperty($this->prototypes->getName());
        $property->setAccessible(true);
        $value = $property->getValue($proxy);
        
        $classGenerator = new OverloadingClassGenerator($className);
        
        foreach($value as $methodName => $methods) {
            foreach($methods as $closure) {
                
                /** ZF2 PR : https://github.com/zendframework/zf2/pull/5245 */
                $reflectionFunction = new \ReflectionFunction($closure);
                
                $lines = array_slice(
                    file($reflectionFunction->getFileName(), FILE_IGNORE_NEW_LINES),
                    $reflectionFunction->getStartLine() - 1,
                    ($reflectionFunction->getEndLine() - ($reflectionFunction->getStartLine() - 1)),
                    true
                );

                $functionLine = implode(' ', $lines);
                if ($reflectionFunction->isClosure()) {
                    preg_match('#^\s*\$[^\=]+=\s*function\s*\([^\)]*\)\s*\{(.*)\}\s*;\s*$#', $functionLine, $matches);
                } else {
                    preg_match('#^\s*function\s*[^\(]+\([^\)]*\)\s*\{(.*)\}\s*$#', $functionLine, $matches);
                }
                
                $body = trim($matches[1]);
                 /** ZF2 PR end */
                
                $r = new \ReflectionFunction($closure);
                $parameters = array();
                foreach($r->getParameters() as $parameter) {
                    $reflectionParameter = new ParameterReflection($closure, $parameter->getName());
                    $parameters[] = ParameterGenerator::fromReflection($reflectionParameter);
                }
                
                $methodGenerator = new MethodGenerator($methodName);
                $methodGenerator->setBody($body);
                $methodGenerator->setParameters($parameters);
                
                $classGenerator->addMethodFromGenerator($methodGenerator);
            }
        }
        
        return $classGenerator->generate();
    }
}
