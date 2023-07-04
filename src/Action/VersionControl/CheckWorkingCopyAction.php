<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\VersionControl;

use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Output\Console;

final class CheckWorkingCopyAction extends BaseAction
{
    private const EXCEPTION_CODE  = 412;

    public function execute(Context $context, Console $console): void
    {
        $modCount = \count($context->getVersionControl()->getLocalModifications());

        if ($modCount > 0) {
            throw new CommandException(sprintf('Your working directory contains %s local modification(s).', $modCount), self::EXCEPTION_CODE);
        }
    }
}
