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

    /**
     * Generates a valid unique identifier from the given name
     *
     * @param string $name
     *
     * @return string
     */
    public static function getIdentifier($name)
    {
        return str_replace(
            '.',
            '',
            uniqid(
                preg_match(static::VALID_IDENTIFIER_FORMAT, $name)
                ? $name
                : static::DEFAULT_IDENTIFIER,
                true
            )
        );
    }
}
