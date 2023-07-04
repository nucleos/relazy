<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\QA;

use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Output\Console;

final class PhpCsFixerAction extends BaseAction
{
    private readonly string $command;

    private readonly string $parameter;

    private readonly int $expectedExitCode;

    private readonly ?int $timeout;

    public function __construct(?string $command = null, ?string $parameter = null, ?int $expectedExitCode = null, ?int $timeout = null)
    {
        $this->command          = $command          ?? 'vendor/bin/php-cs-fixer';
        $this->parameter        = $parameter        ?? 'analyse --verbose --no-interaction';
        $this->expectedExitCode = $expectedExitCode ?? 0;
        $this->timeout          = $timeout;
    }

    public function execute(Context $context, Console $console): void
    {
        $command = $this->command.' '.$this->parameter;

        if ($context->isDryRun()) {
            $command = ' --dry-run';
        }

        $process = $this->executeCommand($console, $command, $this->timeout);

        if ($process->getExitCode() !== $this->expectedExitCode) {
            throw new CommandException('php-cs-fixer failed');
        }
    }
}
