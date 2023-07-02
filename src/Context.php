<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy;

use Nucleos\Relazy\Changelog\Formatter\Formatter;
use Nucleos\Relazy\Interaction\InteractionCollection;
use Nucleos\Relazy\Version\Generator\Generator;
use Nucleos\Relazy\Version\Persister\Persister;
use Nucleos\Relazy\VersionControl\VersionControl;

final class Context
{
    /**
     * @deprecated
     */
    public Generator $versionGenerator;

    /**
     * @deprecated
     */
    public Persister $versionPersister;

    /**
     * @deprecated
     */
    public Formatter $formatter;

    private readonly InteractionCollection $interactionCollection;

    private readonly VersionControl $versionControl;

    private readonly string $projectRoot;

    private bool $dryRun = false;

    private ?string $currentVersion = null;

    private ?string $nextVersion = null;

    private ?string $initialVersion = null;

    private ?string $releaseVersion = null;

    private ?string $versionRegexPattern = null;

    public function __construct(InteractionCollection $interactionCollection, VersionControl $vcs, string $projectRoot)
    {
        $this->interactionCollection = $interactionCollection;
        $this->versionControl        = $vcs;
        $this->projectRoot           = $projectRoot;
    }

    public function getInitialVersion(): ?string
    {
        return $this->initialVersion;
    }

    public function setInitialVersion(string $initialVersion): void
    {
        $this->initialVersion = $initialVersion;
    }

    public function getReleaseVersion(): ?string
    {
        return $this->releaseVersion;
    }

    public function setReleaseVersion(string $releaseVersion): void
    {
        $this->releaseVersion = $releaseVersion;
    }

    public function getVersionRegexPattern(): ?string
    {
        return $this->versionRegexPattern;
    }

    public function setVersionRegexPattern(string $versionRegex): void
    {
        $this->versionRegexPattern = $versionRegex;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function getInformationCollection(): InteractionCollection
    {
        return $this->interactionCollection;
    }

    public function getCurrentVersion(): ?string
    {
        return $this->currentVersion;
    }

    public function setCurrentVersion(?string $currentVersion): void
    {
        $this->currentVersion = $currentVersion;
    }

    public function getNextVersion(): ?string
    {
        return $this->nextVersion;
    }

    public function setNextVersion(?string $nextVersion): void
    {
        $this->nextVersion = $nextVersion;
    }

    public function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    public function getVersionControl(): VersionControl
    {
        return $this->versionControl;
    }
}
