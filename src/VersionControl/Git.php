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

use Nucleos\Relazy\Exception\RelazyException;
use Symfony\Component\Process\Process;

final class Git implements VersionControl
{
    private const DEFAULT_REMOTE = 'origin';

    private readonly bool $signTag;

    private readonly bool $signCommit;

    private readonly bool $dryRun;

    public function __construct(?bool $signCommit = null, ?bool $signTag = null, ?bool $dryRun = null)
    {
        $this->signCommit = $signCommit ?? false;
        $this->signTag    = $signTag    ?? false;
        $this->dryRun     = $dryRun     ?? false;
    }

    public function getAllModificationsSince(string $tag, bool $color = true, bool $noMergeCommits = false): array
    {
        $colorOption          = $color ? '--color=always' : '';
        $noMergeCommitsOption = $noMergeCommits ? '--no-merges' : '';

        return $this->executeGitCommand(sprintf('log --oneline %s..HEAD %s %s', $tag, $colorOption, $noMergeCommitsOption));
    }

    public function getModifiedFilesSince(string $tag): array
    {
        $files = [];

        $lines  = array_filter($this->executeGitCommand(sprintf('diff --name-status %s..HEAD', $tag)));

        foreach ($lines as $line) {
            [$state, $file] = explode("\t", (string) $line);

            $files[$file] = $state;
        }

        return $files;
    }

    public function getLocalModifications(): array
    {
        return $this->executeGitCommand('status -s');
    }

    public function getTags(): array
    {
        return $this->executeGitCommand('tag');
    }

    public function createTag(string $name): void
    {
        $this->executeGitCommand(sprintf('tag %s %s -m %s', $this->signTag ? '-s' : '', $name, $name));
    }

    public function publishTag(string $tagName, ?string $remote = null): void
    {
        $remote ??= self::DEFAULT_REMOTE;

        $this->executeGitCommand(sprintf('push %s %s', $remote, $tagName));
    }

    public function publishChanges(?string $remote = null): void
    {
        $remote ??= self::DEFAULT_REMOTE;

        $this->executeGitCommand(sprintf('push %s ', $remote).$this->getCurrentBranch());
    }

    public function saveWorkingCopy(string $commitMsg = '', array $filter = []): void
    {
        if ([] === $filter) {
            $this->executeGitCommand('add --all');
        } else {
            $this->executeGitCommand('add '.implode(' ', $filter));
        }

        $this->executeGitCommand(sprintf('commit %s -m "%s"', $this->signCommit ? '-S' : '', $commitMsg));
    }

    public function getCurrentBranch(): string
    {
        $branches = $this->executeGitCommand('branch');

        foreach ($branches as $branch) {
            if (!str_starts_with((string) $branch, '* ')) {
                continue;
            }

            if (1 === preg_match('/^\*\s\(.*\)$/', (string) $branch)) {
                continue;
            }

            return substr((string) $branch, 2);
        }

        throw new RelazyException('Not currently on any branch');
    }

    /**
     * @return array<int, mixed>
     */
    private function executeGitCommand(string $command): array
    {
        if ($this->dryRun && 'tag' !== $command) {
            $cmdWords = explode(' ', $command);

            if (\in_array($cmdWords[0], ['tag', 'push', 'add', 'commit'], true)) {
                return [];
            }
        }

        $process = Process::fromShellCommandline(sprintf('git %s', $command));
        $process->run();

        $result = explode(PHP_EOL, $process->getOutput());

        if (!$process->isSuccessful()) {
            throw new RelazyException('Error while executing git command: git '.$command."\n".implode("\n", $result));
        }

        return $result;
    }
}
