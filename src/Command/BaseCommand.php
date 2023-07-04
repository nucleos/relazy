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
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Exception\NoReleaseFoundException;
use Nucleos\Relazy\Exception\RelazyException;
use Nucleos\Relazy\Interaction\InteractionCollector;
use Nucleos\Relazy\Output\Console;
use Nucleos\Relazy\Output\OutputConsole;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper/helper around Symfony command.
 */
abstract class BaseCommand extends Command
{
    private const CONFIG_FILE = '.relazy.php';

    protected readonly InteractionCollector $informationCollector;

    private ?Context $context = null;

    private ?RelazyConfig $config = null;

    public function __construct(string $name = null)
    {
        $this->informationCollector = new InteractionCollector();

        parent::__construct($name);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        chdir($this->getProjectRootDir());
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config  = $this->getConfig();

        $context = $this->getContext();

        $console = $this->createOutputConsole($input, $output);

        return $this->internalExecute($input, $console, $context, $config);
    }

    abstract protected function internalExecute(
        InputInterface $input,
        Console $console,
        Context $context,
        RelazyConfig $config
    ): int;

    protected function getContext(): Context
    {
        if (null === $this->context) {
            $config  = $this->getConfig();

            $context                   = new Context($this->informationCollector, $config->getVersionControl(), $this->getProjectRootDir());
            $context->versionGenerator = $config->getGenerator();
            $context->versionPersister = $config->getPersister();
            $context->formatter        = $config->getFormatter();
            $context->setInitialVersion($config->getGenerator()->getInitialVersion());
            $context->setVersionRegexPattern($config->getGenerator()->getValidationRegex());
            $context->setCurrentVersion($this->getCurrentVersion($context));

            $this->context = $context;
        }

        return $this->context;
    }

    protected function getConfig(): RelazyConfig
    {
        if (null === $this->config) {
            $configFile = $this->getConfigFilePath();

            if (null === $configFile || !is_file($configFile)) {
                throw new RelazyException(sprintf('Impossible to locate the config file at %s.', $configFile));
            }

            try {
                $config = require $configFile;
            } catch (Exception) {
                $config = null;
            }

            if (!$config instanceof RelazyConfig) {
                throw new RelazyException(sprintf('Impossible to load config file (%s)', $configFile));
            }

            $this->config = $config;
        }

        return $this->config;
    }

    protected function getCurrentVersion(Context $context): ?string
    {
        try {
            $currentVersion = $context->versionPersister->getCurrentVersion($context);
        } catch (NoReleaseFoundException) {
            $currentVersion = $context->getInitialVersion();
        }

        if (null === $currentVersion) {
            throw CommandOrderException::forField('current version');
        }

        return $currentVersion;
    }

    protected function createOutputConsole(InputInterface $input, OutputInterface $output): OutputConsole
    {
        $console = new OutputConsole($input, $output);
        // @phpstan-ignore-next-line
        $console->setDialogHelper($this->getHelper('question'));
        // @phpstan-ignore-next-line
        $console->setFormatterHelper($this->getHelper('formatter'));

        return $console;
    }

    private function getProjectRootDir(): string
    {
        if (\defined('RELAZY_ROOT_DIR')) {
            return RELAZY_ROOT_DIR;
        }

        $dir = getcwd();

        if (false === $dir) {
            throw new RelazyException('Could not determine working directory');
        }

        return $dir;
    }

    private function getConfigFilePath(): ?string
    {
        if (file_exists($path = $this->getProjectRootDir().\DIRECTORY_SEPARATOR.self::CONFIG_FILE)) {
            return $path;
        }

        return null;
    }
}
