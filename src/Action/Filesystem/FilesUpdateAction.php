<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\Filesystem;

use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Exception\ConfigException;
use Nucleos\Relazy\Output\Console;

final class FilesUpdateAction extends BaseAction
{
    /**
     * @var array<string, string>
     */
    private readonly array $files;

    /**
     * @param array<string, string> $files
     */
    public function __construct(array $files)
    {
        if ([] === $files) {
            throw new ConfigException('You must specify at least one file');
        }

        $this->files = $files;
    }

    public function execute(Context $context, Console $console): void
    {
        $currentVersion = $context->getCurrentVersion();
        $nextVersion    = $context->getNextVersion();

        if (null === $currentVersion) {
            throw CommandOrderException::forField('current version');
        }

        if (null === $nextVersion) {
            throw CommandOrderException::forField('next version');
        }

        foreach ($this->files as $file => $pattern) {
            if (file_exists($file)) {
                throw new CommandException(sprintf('File %s does not exist', $file));
            }

            if ($context->isDryRun()) {
                $console->writeWarning(sprintf('Skipping "%s" file update', $file));

                continue;
            }

            $this->updateFile($file, $pattern, $currentVersion, $nextVersion);
        }
    }

    private function updateFile(string $filename, ?string $pattern, string $currentVersion, string $nextVersion): void
    {
        $content = file_get_contents($filename);

        if (false === $content) {
            throw new CommandException(sprintf('The %s file could not be loaded', $filename));
        }

        if (!str_contains($content, $currentVersion)) {
            throw new CommandException('The version file '.$filename.' does not contain the current version '.$currentVersion);
        }

        if (null !== $pattern) {
            $currentVersion = str_replace('%version%', $currentVersion, $pattern);
            $nextVersion    = str_replace('%version%', $nextVersion, $pattern);
        }

        $content = str_replace($currentVersion, $nextVersion, $content);

        if (!str_contains($content, $nextVersion)) {
            throw new CommandException(sprintf('The version file %s could not be updated with version %s', $filename, $nextVersion));
        }

        file_put_contents($filename, $content);
    }
}
