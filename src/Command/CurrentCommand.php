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

final class CurrentCommand extends BaseCommand
{
    private const VCS_TAG = 'vcs-tag';

    private const RAW = 'raw';

    protected static $defaultName = 'current';

    protected function configure(): void
    {
        $this->setDescription('Display information about the current release');
        $this->setHelp('The <comment>current</comment> task can be used to display information on the current release');
        $this->addOption(self::RAW, null, InputOption::VALUE_NONE, 'display only the version name');
        $this->addOption(self::VCS_TAG, null, InputOption::VALUE_NONE, 'display the associated vcs-tag');
    }

    protected function internalExecute(
        InputInterface $input,
        Console $console,
        Context $context,
        RelazyConfig $config
    ): int {
        $isTag = true === $input->getOption(self::VCS_TAG);

        try {
            $version = $config->getPersister()->getCurrentVersion($context);
        } catch (NoReleaseFoundException) {
            $console->writeLine('There is no existing tag');

            return Command::SUCCESS;
        }

        $vcsTag = null;
        if ($isTag) {
            $vcsTag = $config->getPersister()->getCurrentVersionTag($context);
        }

        if (true === $input->getOption(self::RAW)) {
            $console->writeLine($isTag ? $vcsTag : $version);
        } else {
            $msg = sprintf('Current release is: <green>%s</green>', $version);

            if ($isTag) {
                $msg .= sprintf(' (VCS tag: <green>%s</green>)', $vcsTag);
            }

            $console->writeLine($msg);
        }

        return Command::SUCCESS;
    }
}
