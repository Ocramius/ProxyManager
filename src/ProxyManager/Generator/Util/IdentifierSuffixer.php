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

use PackageVersions\Versions;

/**
 * Utility class capable of generating
 * valid class/property/method identifiers
 * with a deterministic attached suffix,
 * in order to prevent property name collisions
 * and tampering from userland
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class IdentifierSuffixer
{
    const VALID_IDENTIFIER_FORMAT = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/';
    const DEFAULT_IDENTIFIER = 'g';

    private final function __construct()
    {
    }

    /**
     * Generates a valid unique identifier from the given name,
     * with a suffix attached to it
     */
    public static function getIdentifier(string $name) : string
    {
        static $salt;

        $salt   = $salt ?? $salt = self::loadBaseHashSalt();
        $suffix = \substr(\sha1($name . $salt), 0, 5);

        if (! preg_match(self::VALID_IDENTIFIER_FORMAT, $name)) {
            return self::DEFAULT_IDENTIFIER . $suffix;
        }

        return $name . $suffix;
    }

    private static function loadBaseHashSalt() : string
    {
        return \sha1(\serialize(Versions::VERSIONS));
    }
}
