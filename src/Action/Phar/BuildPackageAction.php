<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\Phar;

use FilesystemIterator;
use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Output\Console;
use Phar;

final class BuildPackageAction extends BaseAction
{
    private readonly string $destination;

    private readonly string $packageName;

    private readonly ?string $excludedPaths;

    /**
     * @var array<string, string>
     */
    private readonly array $metadata;

    private readonly ?string $defaultStubCli;

    private readonly ?string $defaultStubWeb;

    /**
     * @param array<string, string>|null $metadata
     */
    public function __construct(
        string $destination,
        string $packageName,
        ?string $excludedPaths = null,
        ?array $metadata = null,
        ?string $defaultStubCli = null,
        ?string $defaultStubWeb = null
    ) {
        $this->destination    = $destination;
        $this->packageName    = $packageName;
        $this->excludedPaths  = $excludedPaths;
        $this->metadata       = $metadata ?? [];
        $this->defaultStubCli = $defaultStubCli;
        $this->defaultStubWeb = $defaultStubWeb;
    }

    public function execute(Context $context, Console $console): void
    {
        $outputFile = $this->getDestination($context).'/'.$this->getFilename($context);

        if ($context->isDryRun()) {
            $console->writeWarning(sprintf('Skipping PHAR generation for "%s" file', $outputFile));

            return;
        }

        $phar = new Phar($outputFile, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME);
        $phar->buildFromDirectory($context->getProjectRoot(), $this->excludedPaths ?? '');
        $phar->setMetadata(array_merge(['version' => $context->getReleaseVersion()], $this->metadata));
        $phar->setDefaultStub($this->defaultStubCli, $this->defaultStubWeb);

        $packagePath = $outputFile;

        $console->writeLine('The package has been successfully created in: '.$packagePath);
    }

    private function getFilename(Context $context): string
    {
        return $this->packageName.'-'.$context->getReleaseVersion().'.phar';
    }

    private function isRelativePath(string $path): bool
    {
        return !str_starts_with($path, '/');
    }

    private function getDestination(Context $context): string
    {
        $destination = $this->destination;

        if ($this->isRelativePath($destination)) {
            return $context->getProjectRoot().'/'.$destination;
        }

        return $destination;
    }
}
