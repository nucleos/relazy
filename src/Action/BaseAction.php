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
use Nucleos\Relazy\Output\Console;
use Symfony\Component\Process\Process;

abstract class BaseAction implements Action
{
    abstract public function execute(Context $context, Console $console): void;

    public function getTitle(): string
    {
        $classname = explode('\\', static::class);

        $title = preg_replace('/(?!^)[[:upper:]]+/', '$0', end($classname))   ?? '';
        $title = preg_replace('/(Action)?$/', '', $title)                     ?? '';
        $title = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', ' $0', $title) ?? '';

        return trim($title);
    }

    /**
     * Execute a command and render the output through the classical indented output.
     */
    final protected function executeCommand(Console $console, string $command, float $timeout = null): Process
    {
        $console->writeLine(sprintf("<comment>%s</comment>\n\n", $command));

        $process = Process::fromShellCommandline($command);

        if (null !== $timeout) {
            $process->setTimeout($timeout);
        }

        $process->run(static function ($type, $buffer) use ($console): void {
            $console->write($buffer);
        });

        return $process;
    }
}
