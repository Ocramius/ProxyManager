<?php

declare(strict_types=1);

/**
 * @see https://github.com/laminas/laminas-server for the canonical source repository
 */

namespace Laminas\Server;

interface Client
{
    public function call(string $method, array $params = []);
}
