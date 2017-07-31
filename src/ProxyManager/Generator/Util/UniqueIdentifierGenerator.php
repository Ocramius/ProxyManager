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

namespace ProxyManager\Generator\Util;

/**
 * Utility class capable of generating unique
 * valid class/property/method identifiers
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class UniqueIdentifierGenerator
{
    const VALID_IDENTIFIER_FORMAT = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/';
    const DEFAULT_IDENTIFIER = 'g';

    private static $uniqId;

    /**
     * Generates a valid unique identifier from the given name
     */
    public static function getIdentifier(string $name, $groupByName = false) : string
    {
        if (null === self::$uniqId) {
            self::$uniqId = str_replace('.', '', uniqid('', true));
        }

        if (preg_match(static::VALID_IDENTIFIER_FORMAT, $name)) {
            $uniqId = $groupByName ? self::$uniqId : str_replace('.', '', uniqid('', true));
        } else {
            $name = static::DEFAULT_IDENTIFIER;
            $uniqId = str_replace('.', '', uniqid('', true));
        }

        return $name.$uniqId;
    }
}
