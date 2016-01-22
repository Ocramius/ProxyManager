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

namespace ProxyManager\ProxyGenerator;

use ProxyManager\Generator\MethodGenerator as ProxyManagerMethodGenerator;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\GhostObjectInterface}
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingGhostGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = [])
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $filteredProperties = Properties::fromReflectionClass($originalClass)
            ->filter(isset($proxyOptions['skippedProperties']) ? $proxyOptions['skippedProperties'] : []);

        $publicProperties    = new PublicPropertiesMap($filteredProperties);
        $privateProperties   = new PrivatePropertiesMap($filteredProperties);
        $protectedProperties = new ProtectedPropertiesMap($filteredProperties);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([GhostObjectInterface::class]);
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($initializationTracker = new InitializationTracker());
        $classGenerator->addPropertyFromGenerator($publicProperties);
        $classGenerator->addPropertyFromGenerator($privateProperties);
        $classGenerator->addPropertyFromGenerator($protectedProperties);

        $init = new CallInitializer($initializer, $initializationTracker, $filteredProperties);

        array_map(
            function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                $this->getAbstractProxiedMethods($originalClass),
                [
                    $init,
                    new StaticProxyConstructor($initializer, $filteredProperties),
                    new MagicGet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties,
                        $initializationTracker
                    ),
                    new MagicSet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicClone($originalClass, $initializer, $init, $publicProperties),
                    new MagicSleep($originalClass, $initializer, $init, $publicProperties),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $init),
                    new IsProxyInitialized($initializer),
                ]
            )
        );
    }

    /**
     * Retrieves all abstract methods to be proxied
     *
     * @param ReflectionClass $originalClass
     *
     * @return MethodGenerator[]
     */
    private function getAbstractProxiedMethods(ReflectionClass $originalClass) : array
    {
        return array_map(
            function (ReflectionMethod $method) : ProxyManagerMethodGenerator {
                return ProxyManagerMethodGenerator
                    ::fromReflection(new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()))
                    ->setAbstract(false);
            },
            ProxiedMethodsFilter::getAbstractProxiedMethods($originalClass)
        );
    }
}
