<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\Composer;

use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Output\Console;

final class OutdatedAction extends AbstractAction
{
    private readonly string $composer;

    public function __construct(?string $composer = null)
    {
        $this->composer = $composer ?? 'composer';
    }

    public function execute(Context $context, Console $console): void
    {
        $process = $this->executeCommand($console, $this->composer.' outdated');

        if (0 !== $process->getExitCode()) {
            throw new CommandException(sprintf('%s is invalid', self::COMPOSER_JSON_FILE));
        }
    }
}
