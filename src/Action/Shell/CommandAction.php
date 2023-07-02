<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\Shell;

use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Output\Console;
use Symfony\Component\Process\Process;

class CommandAction extends BaseAction
{
    private readonly string $command;

    private readonly bool $suppressOutput;

    private readonly bool $stopOnError;

    private readonly ?int $timeout;

    public function __construct(string $command, ?bool $suppressOutput = null, ?bool $stopOnError = null, ?int $timeout = null)
    {
        $this->command        = $command;
        $this->suppressOutput = $suppressOutput ?? false;
        $this->stopOnError    = $stopOnError    ?? true;
        $this->timeout        = $timeout;
    }

    public function execute(Context $context, Console $console): void
    {
        $command = $this->command;

        $console->writeLine(sprintf("<comment>%s</comment>\n\n", $command));

        if ($context->isDryRun()) {
            $console->writeWarning('Skipping execution in dry run mode');

            return;
        }

        $callback = null;
        if (!$this->suppressOutput) {
            $callback = static function ($type, $buffer) use ($console): void {
                $decorator = ['', ''];
                if (Process::ERR === $type) {
                    $decorator = ['<error>', '</error>'];
                }

                $console->writeLine($decorator[0].$buffer.$decorator[1]);
            };
        }

        $process = Process::fromShellCommandline($command);

        if (null !== $timeout = $this->timeout) {
            $process->setTimeout($timeout);
        }

        $process->run($callback);

        if (!$this->stopOnError) {
            return;
        }

        if (0 === $process->getExitCode()) {
            return;
        }

        throw new CommandException(sprintf('Command [%s] exit with code ', $command).$process->getExitCode());
    }
}
