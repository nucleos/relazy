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

final class CommitAction extends BaseAction
{
    private readonly string $commitMessage;

    /**
     * @var string[]
     */
    private readonly array $filter;

    /**
     * @param string[]|null $filter
     */
    public function __construct(?string $commitMessage = null, ?array $filter = null)
    {
        $this->commitMessage = $commitMessage ?? 'Release version %version%';
        $this->filter        = $filter        ?? [];
    }

    public function execute(Context $context, Console $console): void
    {
        if ([] === $context->getVersionControl()->getLocalModifications()) {
            $console->writeWarning('No modification found, aborting commit');

            return;
        }

        if ($context->isDryRun()) {
            $console->writeWarning('Skipping VCS commit');

            return;
        }

        $nextVersion = $context->getNextVersion();

        if (null === $nextVersion) {
            throw CommandOrderException::forField('next version');
        }

        $context->getVersionControl()->saveWorkingCopy(
            str_replace('%version%', $nextVersion, $this->commitMessage),
            $this->filter
        );
    }
}
