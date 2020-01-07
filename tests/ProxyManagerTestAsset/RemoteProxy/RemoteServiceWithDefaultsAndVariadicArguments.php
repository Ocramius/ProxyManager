<?php

/**
 * CRM PROJECT
 * THIS FILE IS A PART OF CRM PROJECT
 * CRM PROJECT IS PROPERTY OF Legal One GmbH
 */

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

interface RemoteServiceWithDefaultsAndVariadicArguments
{
    public function optionalWithVariadic(
        string $required,
        string $optional = 'Optional param to be kept on proxy call',
        int ...$ints
    );
}
