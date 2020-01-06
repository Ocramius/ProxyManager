<?php

declare(strict_types=1);

/**
 * @see       https://github.com/zendframework/zend-server for the canonical source repository
 */

namespace Zend\Server;

/**
 * Client Interface
 */
interface Client
{
    /**
     * Executes remote call
     *
     * Unified interface for calling custom remote methods.
     *
     * @param  string        $method Remote call name.
     * @param  array|mixed[] $params Call parameters.
     *
     * @return mixed Remote call results.
     */
    public function call(string $method, array $params = []);
}
