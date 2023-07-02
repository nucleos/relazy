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

use Exception;
use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Output\Console;

final class CurrentVersionAction extends BaseAction
{
    public function execute(Context $context, Console $console): void
    {
        try {
            $currentVersion = $context->getCurrentVersion();

            $console->writeLine(sprintf('<yellow>%s</yellow>', $currentVersion));
        } catch (Exception $e) {
            $console->writeWarning('No modification found: '.$e->getMessage());
        }
    }
}
