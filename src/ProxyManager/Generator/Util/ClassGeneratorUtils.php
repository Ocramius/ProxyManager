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

namespace ProxyManager\Generator\Util;

use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ClassGenerator as GeneratorClass;

/**
 * Util class to help to generate code
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
final class ClassGeneratorUtils
{
    /**
     * @param ReflectionClass  $originalClass
     * @param GeneratorClass   $classGenerator
     * @param MethodGenerator  $generatedMethod
     *
     * @return void|false
     */
    public static function addMethodIfNotFinal(
        ReflectionClass $originalClass,
        GeneratorClass $classGenerator,
        MethodGenerator $generatedMethod
    ) {
        $methodName = $generatedMethod->getName();

        if ($classGenerator->hasMethod($methodName)
            || ($originalClass->hasMethod($methodName) && $originalClass->getMethod($methodName)->isFinal())) {
            return false;
        }

        $classGenerator->addMethodFromGenerator($generatedMethod);
    }

    public static function getAbstractMethods($originalClass)
    {
        $methodList = array();
        $abstractMethods = $originalClass->getMethods(ReflectionMethod::IS_ABSTRACT);
        foreach ($abstractMethods as $method) {
            $methodList[] = $method->getName();
        }

        return $methodList;
    }
}
