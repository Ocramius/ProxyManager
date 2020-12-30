<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\ClassGenerator as LaminasClassGenerator;

/**
 * Class generator that ensures that interfaces/classes that are implemented/extended are FQCNs
 *
 * @deprecated this class was in use due to parent implementation not receiving prompt bugfixes, but
 *             `laminas/laminas-code` is actively maintained and receives quick release iterations.
 *
 * @internal do not use this in your code: it is only here for internal use
 */
class ClassGenerator extends LaminasClassGenerator
{
}
