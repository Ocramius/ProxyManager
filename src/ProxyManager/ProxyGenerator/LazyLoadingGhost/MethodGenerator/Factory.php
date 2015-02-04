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
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;

/**
 * Factory to centralize creation of methods.
 *
 * @author  Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class Factory
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
        InitializerProperty $initializerProperty,
        CallInitializer $callInitializer,
        PublicPropertiesMap $publicProperties,
        ProtectedPropertiesMap $protectedProperties,
        PrivatePropertiesMap $privateProperties,
        InitializationTracker $initializationTracker
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
    public function magicGet()
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
    public function magicSet()
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
    public function magicIsset()
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
    public function magicUnset()
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
    public function magicClone()
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
    public function magicSleep()
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
}
