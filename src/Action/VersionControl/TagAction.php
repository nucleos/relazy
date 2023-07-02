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
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Output\Console;

final class TagAction extends BaseAction
{
    public function execute(Context $context, Console $console): void
    {
        $nextVersion = $context->getNextVersion();

        if (null === $nextVersion) {
            throw CommandOrderException::forField('next version');
        }

        if ($context->isDryRun()) {
            $console->writeWarning(sprintf('Skipping tagging. Next text would be %s', $nextVersion));

            return;
        }

        $context->getVersionControl()->createTag(
            $context->versionPersister->getTagFromVersion($nextVersion, $context)
        );
    }
}
