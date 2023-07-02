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

interface VersionControl
{
    public function getCurrentBranch(): string;

    /**
     * @return string[]
     */
    public function getTags(): array;

    public function createTag(string $name): void;

    public function publishTag(string $tagName, ?string $remote = null): void;

    /**
     * @return array<array-key, string>
     */
    public function getAllModificationsSince(string $tag, bool $color = true, bool $noMergeCommits = false): array;

    /**
     * Return the list of all modified files from the given tag until now
     * The result is an array with the filename as key and the status as value.
     * Status is one of the following : M (modified), A (added), R (removed).
     *
     * @return array<string, string>
     */
    public function getModifiedFilesSince(string $tag): array;

    /**
     * @return string[] files of local modification
     */
    public function getLocalModifications(): array;

    /**
     * Save the local modifications (commit).
     *
     * @param string[] $filter
     */
    public function saveWorkingCopy(string $commitMsg = '', array $filter = []): void;

    public function publishChanges(?string $remote = null): void;
}
