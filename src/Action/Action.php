<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action;

use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Output\Console;

interface Action
{
    /**
     * Return the name of the action as it will be displayed to the user.
     */
    public function getTitle(): string;

    /**
     * @throws CommandException
     */
    public function execute(Context $context, Console $console): void;
}
