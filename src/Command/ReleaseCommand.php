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

use Exception;
use Nucleos\Relazy\Action\Action;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Exception\NoReleaseFoundException;
use Nucleos\Relazy\Interaction\InteractionRequest;
use Nucleos\Relazy\Interaction\InteractionRequestAware;
use Nucleos\Relazy\Interaction\InteractionType;
use Nucleos\Relazy\Output\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ReleaseCommand extends BaseCommand
{
    private const CONFIRM_FIRST = 'confirm-first';

    private const DRY_RUN = 'dry-run';

    protected static $defaultName = 'release';

    protected function configure(): void
    {
        $this->setDescription('Release a new version of the project');
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project');
        $this->addOption(self::DRY_RUN, null, InputOption::VALUE_NONE, 'Execute all steps without changing anything');

        $this->loadInformationCollector();

        foreach ($this->informationCollector->getCommandOptions() as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $io = new SymfonyStyle($input, $output);
        $io->title('relazy - The lazy release tool');

        $config  = $this->getConfig();
        $console = $this->createOutputConsole($input, $output);

        $this->executeActions($console, $config->getStartupActions(), 'Startup');

        $this->informationCollector->handleCommandInput($input);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->informationCollector->hasMissingInformation()) {
            return;
        }

        $questions = $this->informationCollector->getInteractiveQuestions();

        $console = $this->createOutputConsole($input, $output);
        $console->indent();

        $count = 1;
        foreach ($questions as $name => $question) {
            $answer = $console->askQuestion($question, $count++, $input);
            $this->informationCollector->setValue($name, $answer);

            $console->writeLine();
        }

        $console->unindent();
    }

    protected function internalExecute(
        InputInterface $input,
        Console $console,
        Context $context,
        RelazyConfig $config
    ): int {
        $context->setDryRun(true === $input->getOption(self::DRY_RUN));

        $newVersion = $config->getGenerator()->generateNextVersion($context);
        $context->setNextVersion($newVersion);

        $this->executeActions($console, $config->getPreReleaseActions(), 'Pre-Release');

        $console->writeTitle('Release process');
        $console->indent();

        if ($context->isDryRun()) {
            $console->writeWarning(sprintf('Skipping creation of a new VCS tag [<yellow>%s</yellow>]', $newVersion));
        } else {
            $console->writeLine(sprintf('A new version named [<yellow>%s</yellow>] is going to be released', $newVersion));

            $tag = $context->versionPersister->save($newVersion, $context);

            $console->writeLine(sprintf('Creation of a new VCS tag [<yellow>%s</yellow>]', $tag));
            $console->writeLine('Release: <green>Success</green>');
        }

        $console->unindent();

        $this->executeActions($console, $config->getPostReleaseActions(), 'Post-Release');

        return Command::SUCCESS;
    }

    protected function getCurrentVersion(Context $context): string
    {
        try {
            $currentVersion = $context->versionPersister->getCurrentVersion($context);
        } catch (NoReleaseFoundException $e) {
            if (false === $this->informationCollector->getValue(self::CONFIRM_FIRST)) {
                throw $e;
            }

            $currentVersion = $context->getInitialVersion();
        }

        if (null === $currentVersion) {
            throw CommandOrderException::forField('current version');
        }

        return $currentVersion;
    }

    /**
     * @param Action[] $actions
     */
    private function executeActions(Console $output, array $actions, string $title): void
    {
        $context = $this->getContext();

        if ([] === $actions) {
            return;
        }

        $output->writeTitle($title);
        $output->indent();

        foreach ($actions as $num => $action) {
            $output->writeLine(sprintf('%s) %s', (int) $num+1, $action->getTitle()));
            $output->writeLine();
            $output->indent();

            $action->execute($context, $output);

            $output->writeLine();
            $output->unindent();
        }

        $output->unindent();
    }

    private function loadInformationCollector(): void
    {
        $context = $this->getContext();
        $config  = $this->getConfig();

        try {
            $config->getPersister()->getCurrentVersion($context);
        } catch (NoReleaseFoundException) {
            $this->informationCollector->registerRequest(
                new InteractionRequest(self::CONFIRM_FIRST, InteractionType::CONFIRMATION, [
                    'description' => 'This is the first release for the current branch',
                ])
            );
        } catch (Exception) {
        }

        $this->bindInteractionRequests($config->getGenerator());
        $this->bindInteractionRequests($config->getPersister());

        foreach ($config->getPreReleaseActions() as $action) {
            $this->bindInteractionRequests($action);
        }

        foreach ($config->getPostReleaseActions() as $action) {
            $this->bindInteractionRequests($action);
        }
    }

    private function bindInteractionRequests(object $object): void
    {
        if ($object instanceof InteractionRequestAware) {
            $this->informationCollector->registerRequests($object->getInteractionRequest());
        }
    }
}
