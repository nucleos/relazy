<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Exception;

class ConfigException extends RelazyException
{
    public function __construct(string $message)
    {
        parent::__construct('Config error: '.$message);
    }
}
