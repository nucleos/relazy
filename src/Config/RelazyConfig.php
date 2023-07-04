<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Config;

use Nucleos\Relazy\Action\Action;
use Nucleos\Relazy\Changelog\Formatter\Formatter;
use Nucleos\Relazy\Exception\RelazyException;
use Nucleos\Relazy\Version\Generator\Generator;
use Nucleos\Relazy\Version\Persister\Persister;
use Nucleos\Relazy\VersionControl\Noop;
use Nucleos\Relazy\VersionControl\VersionControl;

class RelazyConfig
{
    private readonly VersionControl $vcs;

    private ?Generator $generator = null;

    private ?Persister $persister = null;

    private ?Formatter $formatter = null;

    /**
     * @var Action[]
     */
    private array $startupActions = [];

    /**
     * @var Action[]
     */
    private array $preReleaseActions = [];

    /**
     * @var Action[]
     */
    private array $postReleaseActions = [];

    public function __construct(VersionControl $vcs = null)
    {
        $this->vcs = $vcs ?? new Noop();
    }

    /**
     * @param Action[] $actions
     */
    public function startupActions(array $actions): self
    {
        $this->startupActions = $actions;

        return $this;
    }

    /**
     * @param Action[] $actions
     */
    public function preReleaseActions(array $actions): self
    {
        $this->preReleaseActions = $actions;

        return $this;
    }

    public function versionGenerator(Generator $versionGenerator): self
    {
        $this->generator = $versionGenerator;

        return $this;
    }

    public function versionPersister(Persister $versionPersister): self
    {
        $this->persister = $versionPersister;

        return $this;
    }

    /**
     * @param Action[] $actions
     */
    public function postReleaseActions(array $actions): self
    {
        $this->postReleaseActions = $actions;

        return $this;
    }

    public function formatter(Formatter $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function getVersionControl(): VersionControl
    {
        return $this->vcs;
    }

    public function getGenerator(): Generator
    {
        if (null === $this->generator) {
            throw new RelazyException('No generator was defined in your configuration file');
        }

        return $this->generator;
    }

    public function getPersister(): Persister
    {
        if (null === $this->persister) {
            throw new RelazyException('No generator was defined in your configuration file');
        }

        return $this->persister;
    }

    public function getFormatter(): Formatter
    {
        if (null === $this->formatter) {
            throw new RelazyException('No formatter was defined in your configuration file');
        }

        return $this->formatter;
    }

    /**
     * @return Action[]
     */
    public function getStartupActions(): array
    {
        return $this->startupActions;
    }

    /**
     * @return Action[]
     */
    public function getPreReleaseActions(): array
    {
        return $this->preReleaseActions;
    }

    /**
     * @return Action[]
     */
    public function getPostReleaseActions(): array
    {
        return $this->postReleaseActions;
    }
}
