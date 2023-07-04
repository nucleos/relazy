<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Changelog\Formatter;

use Nucleos\Relazy\Version\ReleaseType;

/**
 * Format a changelog file in the "keep a changelog" format. Here is an example:.
 *
 * ## [1.1.0] - 2023-03-05
 *
 * ### Added
 *
 * - [ada96f3] commit msg
 *
 * ### Fixed
 *
 * - [ada96f3] commit msg
 * - [ada96f3] commit msg
 *
 * ### Changed
 *
 * - [ada96f3] commit msg
 *
 * ### Removed
 *
 * - [ada96f3] commit msg
 * - [ada96f3] commit msg
 * - [ada96f3] commit msg
 *
 * ## [1.0.1] - 2019-02-15
 *
 * ### Fixed
 *
 * - [ada96f3] commit msg
 * - [ada96f3] commit msg
 *
 * ## [1.0.0] - 2017-06-20
 *
 * ### Added
 *
 * - New visual identity.
 */
final class PrefixGroupFormatter implements Formatter
{
    private readonly string $defaultGroup;

    /**
     * @var array<string, mixed>
     */
    private readonly array $groups;

    /**
     * @var string[]
     */
    private readonly array $ignorePrefixes;

    /**
     * @param string[]|null $groups
     * @param string[]|null $ignorePrefixes
     */
    public function __construct(?string $defaultGroup = null, ?array $groups = null, ?array $ignorePrefixes = null)
    {
        $this->defaultGroup   = $defaultGroup ?? 'CHANGED';
        $this->groups         = $this->buildGroups($groups ?? []);
        $this->ignorePrefixes = $ignorePrefixes ?? [];
    }

    public function updateExistingLines(
        array $lines,
        string $version,
        ?ReleaseType $releaseType,
        ?string $comment,
        array $extraLines = []
    ): array {
        $pos = $this->findPositionToInsert($lines);

        $ignore  = array_map(static function ($group): string {return strtoupper($group); }, $this->ignorePrefixes);

        if ([] !== $extraLines) {
            $lineGroups = $this->parseLines($extraLines, $this->groups);

            array_splice($lines, $pos, 0, $this->formatExtraLines($lineGroups, $ignore));
        }

        array_splice($lines, $pos, 0, [
            $this->formatRelease($version, $comment),
            '',
        ]);

        return $lines;
    }

    public function getLastVersionRegex(): string
    {
        return '#.*#';
    }

    private function getFormattedDate(): string
    {
        return date('d/m/Y H:i');
    }

    /**
     * @param array<array-key, string> $lines
     */
    private function findPositionToInsert(array $lines): int
    {
        foreach ($lines as $pos => $line) {
            if (1 === preg_match('/## \[\d+\.\d+\.\d+\]\s\-/', $line)) {
                return max(0, (int) $pos);
            }
        }

        return 0;
    }

    private function formatRelease(string $version, ?string $comment): string
    {
        return trim(sprintf('## [%s] - %s %s', $version, $this->getFormattedDate(), $comment ?? ''));
    }

    /**
     * @param string[] $groups
     *
     * @return array<string, mixed>
     */
    private function buildGroups(array $groups): array
    {
        $result = [];

        foreach ($groups as $group) {
            $result[strtoupper($group)] = [];
        }

        return $result;
    }

    /**
     * @param string[]                $lines
     * @param array<string, string[]> $groupedLines
     *
     * @return array<string, string[]>
     */
    private function parseLines(array $lines, array $groupedLines): array
    {
        foreach ($lines as $line) {
            if ('' === $line) {
                continue;
            }

            preg_match('/^([0-9A-f]+) (?:\[(.*)\] )?(.*)/', $line, $matches, PREG_UNMATCHED_AS_NULL);

            $type = strtoupper($matches[2] ?? $this->defaultGroup);
            $line = sprintf('- [%s] %s', $matches[1], $matches[3]);

            $groupedLines[$type][] = $line;
        }

        return $groupedLines;
    }

    /**
     * format extra lines (such as commit details).
     *
     * @param array<string, string[]> $groupedLines
     * @param string[]                $ignore
     *
     * @return string[]
     */
    private function formatExtraLines(array $groupedLines, array $ignore): array
    {
        $result = [];

        foreach ($groupedLines as $key => $valueLines) {
            if (\in_array($key, $ignore, true)) {
                continue;
            }

            if ([] === $valueLines) {
                continue;
            }

            $result[] = sprintf('### %s', $key);
            $result[] = '';

            foreach ($valueLines as $line) {
                $result[] = $line;
            }

            $result[] = '';
        }

        return $result;
    }
}
