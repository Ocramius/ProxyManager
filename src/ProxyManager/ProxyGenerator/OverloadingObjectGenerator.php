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

use ProxyManager\Generator\OverloadingObject\ClassGenerator as OverloadingClassGenerator;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\OverloadingObjectMethodInterceptor;
use ProxyManager\ProxyGenerator\OverloadingObject\PropertyGenerator\PrototypesProperty;
use ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator\MagicCall;
use ProxyManager\Proxy\Exception\OverloadingObjectException;
use ProxyManager\Proxy\OverloadingObjectInterface;
use ProxyManager\Generator\ParameterGenerator;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\ReflectionTools;

use ReflectionFunction;
use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Reflection\ParameterReflection;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocBlockGenerator;

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
     * Object Method
     * 
     * @var \ReflectionMethod[]
     */
    protected $methods = array();
    
    /**
     * Default proxy methods
     * 
     * @var array
     */
    protected $defaultMethods = array();
    
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
        
        $prototypes = $this->getPrototypes();
        $interfaces = array('ProxyManager\\Proxy\\OverloadingObjectInterface');
        foreach ($originalClass->getInterfaceNames() as $name) {
            $interfaces[] = $name;
        }
        
        $defaultProperties = $originalClass->getDefaultProperties();
        foreach($originalClass->getProperties() as $propertyReflection) {
            $property = new PropertyGenerator($propertyReflection->getName(), $defaultProperties[$propertyReflection->getName()]);
            if ($propertyReflection->isPublic()) {
                $property->setVisibility(PropertyGenerator::FLAG_PUBLIC);
            } else if ($propertyReflection->isPrivate()) {
                $property->setVisibility(PropertyGenerator::FLAG_PRIVATE);
            } else if ($propertyReflection->isProtected()) {
                $property->setVisibility(PropertyGenerator::FLAG_PROTECTED);
            }
            $classGenerator->addPropertyFromGenerator($property);
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($prototypes);

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
        
        $classGenerator->addMethodFromGenerator(new MagicCall($originalClass, $prototypes));
        
        /* @var $methods \ReflectionMethod[] */
        $this->methods = array_filter(
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
        
        $list = array();
        foreach ($this->methods as $method) {
            $methodName    = $method->getName();
            $list[]        = $methodName;
            $defautMethods = isset($this->defaultMethods[$methodName]) ? $this->defaultMethods[$methodName] : array();
            
            $classGenerator->addMethodFromGenerator(
                OverloadingObjectMethodInterceptor::generateMethod(
                    $prototypes,
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                    $defautMethods
                )
            );
        }
        
        foreach ($this->defaultMethods as $defaultMethodName => $defautMethods) {
            if (in_array($defaultMethodName, $list)) {
                continue;
            }
            
            $classGenerator->addMethodFromGenerator(
                OverloadingObjectMethodInterceptor::generateFunction(
                    $prototypes,
                    $defaultMethodName,
                    $defautMethods
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
        $prototypes = $this->getPrototypes();
        $property   = $reflection->getProperty($prototypes->getName());
        $property->setAccessible(true);
        $value      = $property->getValue($proxy);
        $property->setAccessible(false);
        
        $classGenerator = new OverloadingClassGenerator($className);
        foreach($this->methods as $method) {
            $reflectionMethod = new MethodReflection($method->getDeclaringClass()->getName(), $method->getName());
            $methodGenerator = MethodGenerator::fromReflection($reflectionMethod);
            $classGenerator->addMethodFromGenerator($methodGenerator);
        }
        
        $methods = array_merge_recursive($this->defaultMethods, $value);
        foreach($methods as $methodName => $closures) {
            foreach($closures as $closure) {
                
                $body = ReflectionTools::getFunctionContent($closure);
                
                $reflectionFunction = new ReflectionFunction($closure);
                $parameters = array();
                $tags = array();
                foreach($reflectionFunction->getParameters() as $parameter) {
                    $reflectionParameter = new ParameterReflection($closure, $parameter->getName());
                    $parameters[] = ParameterGenerator::fromReflection($reflectionParameter);
                    $tags[] = array('name' => 'param', 'description' => '$' . $parameter->getName());
                }
                
                $methodGenerator = new MethodGenerator($methodName);
                $methodGenerator->setBody($body);
                $methodGenerator->setParameters($parameters);
                $docBlock = new DocBlockGenerator();
                $docBlock->setTags($tags);
                $methodGenerator->setDocBlock($docBlock);
                
                $classGenerator->addMethodFromGenerator($methodGenerator);
            }
        }
        
        return $classGenerator->generate();
    }
    
    /**
     * @return PrototypesProperty
     */
    public function getPrototypes()
    {
        if (null === $this->prototypes) {
            $this->prototypes = new PrototypesProperty();
        }
        return $this->prototypes;
    }
    
    /**
     * Set default proxy methods
     * 
     * @param array $methods
     */
    public function setDefaultMethods(array $methods)
    {
        $this->defaultMethods = array();
        foreach($methods as $methodName => $methods) {
            $this->defaultMethods[$methodName] = is_array($methods) ? $methods : array($methods);
        }
    }
}
