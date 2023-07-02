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

class CommandOrderException extends CommandException
{
    public static function forField(string $field): self
    {
        throw new self(sprintf('The %s is not set. Maybe you have an action ordering issue.', $field));
    }
}
