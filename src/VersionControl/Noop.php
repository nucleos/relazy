<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\VersionControl;

class Noop implements VersionControl
{
    public function getCurrentBranch(): string
    {
        return '';
    }

    public function getTags(): array
    {
        return [];
    }

    public function createTag(string $name): void
    {
    }

    public function publishTag(string $tagName, ?string $remote = null): void
    {
    }

    public function getAllModificationsSince(string $tag, bool $color = true, bool $noMergeCommits = false): array
    {
        return [];
    }

    public function getModifiedFilesSince(string $tag): array
    {
        return [];
    }

    public function getLocalModifications(): array
    {
        return [];
    }

    public function saveWorkingCopy(string $commitMsg = '', array $filter = []): void
    {
    }

    public function publishChanges(?string $remote = null): void
    {
    }
}
