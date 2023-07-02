<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Command;

use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\NoReleaseFoundException;
use Nucleos\Relazy\Output\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class ChangesCommand extends BaseCommand
{
    private const EXCLUDE_MERGE_COMMITS = 'exclude-merge-commits';

    private const FILES  = 'files';

    protected static $defaultName = 'changes';

    protected function configure(): void
    {
        $this->setDescription('Shows the list of changes since last release');
        $this->setHelp('The <comment>changes</comment> command is used to list the changes since last release.');
        $this->addOption(self::EXCLUDE_MERGE_COMMITS, null, InputOption::VALUE_NONE, 'Exclude merge commits');
        $this->addOption(self::FILES, null, InputOption::VALUE_NONE, 'Display the list of modified files');
    }

    protected function internalExecute(
        InputInterface $input,
        Console $console,
        Context $context,
        RelazyConfig $config
    ): int {
        try {
            $lastVersion = $config->getPersister()->getCurrentVersionTag($context);
        } catch (NoReleaseFoundException) {
            $console->writeWarning('There is no existing tag');

            return Command::FAILURE;
        }

        $noMerges = $input->getOption(self::EXCLUDE_MERGE_COMMITS);

        if (true === $input->getOption(self::FILES)) {
            $console->writeLine(sprintf('Here is the list of files changed since <green>%s</green>:', $lastVersion));
            $console->writeLine();
            $console->indent();

            $modifications = array_keys($context->getVersionControl()->getModifiedFilesSince($lastVersion));

            foreach ($modifications as $modification) {
                $console->writeLine($modification);
            }

            $console->unindent();

            return Command::SUCCESS;
        }

        $console->writeLine(sprintf('Here is the list of changes since <green>%s</green>:', $lastVersion));
        $console->indent();

        $modifications = $context->getVersionControl()->getAllModificationsSince($lastVersion, false, $noMerges);
        foreach ($modifications as $modification) {
            $console->writeLine($modification);
        }

        $console->unindent();

        return Command::SUCCESS;
    }
}
