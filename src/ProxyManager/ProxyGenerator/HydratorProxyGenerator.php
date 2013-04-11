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
use ProxyManager\ProxyGenerator\Hydrator\MethodGenerator\DisabledMethod;
use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies being a hydrator ({@see \Zend\Stdlib\Hydrator\HydratorInterface})
 * for objects
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorProxyGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $classGenerator->setExtendedClass($originalClass->getName());
        /*$classGenerator->setImplementedInterfaces(
            array(
                 'ProxyManager\\Proxy\\LazyLoadingInterface',
                 'ProxyManager\\Proxy\\ValueHolderInterface',
            )
        );*/

        /* @var $methods ReflectionMethod[] */
        $methods = array_filter(
            $originalClass->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED),
            function (ReflectionMethod $method) {
                return ! (
                    $method->isConstructor()
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

        foreach (array('__get', '__set', '__isset', '__unset', '__clone', '__sleep', '__wakeup') as $magicMethod) {
            // @todo params
            $classGenerator->addMethodFromGenerator(new DisabledMethod($magicMethod));
        }

        // empty constructor
        $classGenerator->addMethodFromGenerator(new MethodGenerator('__construct'));

        // @todo add further parameters
    }
}