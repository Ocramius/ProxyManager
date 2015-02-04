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

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use Zend\Code\Generator\GeneratorInterface;

/**
 * Factory to centralize creation of methods.
 *
 * @author  Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class GeneratorContext
{
    /**
     * @var ReflectionClass
     */
    private $originalClass;

    /**
     * @var InitializerProperty
     */
    private $initializerProperty;

    /**
     * @var CallInitializer
     */
    private $callInitializer;

    /**
     * @var ProtectedPropertiesMap
     */
    private $protectedProperties;

    /**
     * @var PrivatePropertiesMap
     */
    private $privateProperties;

    /**
     * @var PublicPropertiesMap
     */
    private $publicProperties;

    /**
     * @var InitializationTracker
     */
    private $initializationTracker;

    /**
     * Constructor.
     *
     * @param ReflectionClass        $originalClass
     * @param InitializerProperty    $initializerProperty
     * @param CallInitializer        $callInitializer
     * @param PublicPropertiesMap    $publicProperties
     * @param ProtectedPropertiesMap $protectedProperties
     * @param PrivatePropertiesMap   $privateProperties
     * @param InitializationTracker  $initializationTracker
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        GeneratorInterface $callInitializer,
        GeneratorInterface $publicProperties,
        GeneratorInterface $protectedProperties,
        PrivatePropertiesMap $privateProperties = null,
        InitializationTracker $initializationTracker = null
    ) {
        $this->originalClass = $originalClass;
        $this->initializerProperty = $initializerProperty;
        $this->callInitializer = $callInitializer;
        $this->publicProperties = $publicProperties;
        $this->protectedProperties = $protectedProperties;
        $this->privateProperties = $privateProperties;
        $this->initializationTracker = $initializationTracker;
    }

    /**
     * @return MagicGet
     */
    public function getMagicGet()
    {
        return new MagicGet(
            $this->originalClass,
            $this->initializerProperty,
            $this->callInitializer,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties,
            $this->initializationTracker
        );
    }

    /**
     * @return MagicSet
     */
    public function getMagicSet()
    {
        return new MagicSet(
            $this->originalClass,
            $this->initializerProperty,
            $this->callInitializer,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );
    }

    /**
     * @return MagicIsset
     */
    public function getMagicIsset()
    {
        return new MagicIsset(
            $this->originalClass,
            $this->initializerProperty,
            $this->callInitializer,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );
    }

    /**
     * @return MagicUnset
     */
    public function getMagicUnset()
    {
        return new MagicUnset(
            $this->originalClass,
            $this->initializerProperty,
            $this->callInitializer,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );
    }

    /**
     * @return MagicClone
     */
    public function getMagicClone()
    {
        return new MagicClone(
            $this->originalClass,
            $this->initializerProperty,
            $this->callInitializer,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );
    }

    /**
     * @return MagicSleep
     */
    public function getMagicSleep()
    {
        return new MagicSleep(
            $this->originalClass,
            $this->initializerProperty,
            $this->callInitializer,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );
    }

    /**
     * @return SetProxyInitializer
     */
    public function getSetProxyInitializer()
    {
        return new SetProxyInitializer($this->initializerProperty);
    }

    /**
     * @return GetProxyInitializer
     */
    public function getGetProxyInitializer()
    {
        return new GetProxyInitializer($this->initializerProperty);
    }

    /**
     * @return InitializeProxy
     */
    public function getInitializeProxy()
    {
        return new InitializeProxy($this->initializerProperty, $this->callInitializer);
    }

    /**
     * @return IsProxyInitialized
     */
    public function getIsProxyInitialized()
    {
        return new IsProxyInitialized($this->initializerProperty);
    }
}
