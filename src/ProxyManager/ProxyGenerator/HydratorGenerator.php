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

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\DisabledMagicMethod;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\GetAccessorProperties;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\SetAccessorProperties;
use ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator\PropertyAccessor;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\DisabledMethod;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Extract;
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\Hydrate;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies being a hydrator - {@see \Zend\Stdlib\Hydrator\HydratorInterface}
 * for objects
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $interfaces = array('ProxyManager\\Proxy\\HydratorInterface',);

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);

        $excluded = array(
            '__get'    => true,
            '__set'    => true,
            '__isset'  => true,
            '__unset'  => true,
            '__clone'  => true,
            '__sleep'  => true,
            '__wakeup' => true,
        );

        /* @var $methods ReflectionMethod[] */
        $methods = array_filter(
            $originalClass->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED),
            function (ReflectionMethod $method) use ($excluded) {
                return ! (
                    $method->isConstructor()
                    || isset($excluded[strtolower($method->getName())])
                    || $method->isFinal()
                    || $method->isStatic()
                );
            }
        );

        foreach ($methods as $method) {
            $classGenerator->addMethodFromGenerator(
                DisabledMethod::fromReflection(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                )
            );
        }

        foreach (array('__clone', '__sleep', '__wakeup') as $magicMethod) {
            $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, $magicMethod));
        }

        $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, '__get', array('name')));
        $classGenerator->addMethodFromGenerator(
            new DisabledMagicMethod($originalClass, '__set', array('name', 'value'))
        );
        $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, '__isset', array('name')));
        $classGenerator->addMethodFromGenerator(new DisabledMagicMethod($originalClass, '__unset', array('name')));

        $accessibleFlag         = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;
        $accessibleProperties   = $originalClass->getProperties($accessibleFlag);
        $inaccessibleProps      = $originalClass->getProperties(ReflectionProperty::IS_PRIVATE);
        $propertyAccessors      = array();

        foreach ($inaccessibleProps as $inaccessibleProp) {
            $propertyAccessors[] = new PropertyAccessor($inaccessibleProp);
        }

        $classGenerator->addProperties($propertyAccessors);
        $classGenerator->addMethodFromGenerator(new Constructor($originalClass, $propertyAccessors));
        $classGenerator->addMethodFromGenerator(new Hydrate($accessibleProperties, $propertyAccessors));
        $classGenerator->addMethodFromGenerator(new Extract($accessibleProperties, $propertyAccessors));
        $classGenerator->addMethodFromGenerator(new GetAccessorProperties($propertyAccessors));
        $classGenerator->addMethodFromGenerator(new SetAccessorProperties($propertyAccessors));
    }
}
