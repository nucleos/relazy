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

use InvalidArgumentException;
use Nucleos\Relazy\Exception\RelazyException;
use Nucleos\Relazy\Version\ReleaseType;

/**
 * Format a changelog file in a semantic style. Here is an example:.
 *
 *  VERSION 1  MAJOR TITLE
 *  ======================
 *
 *    Version 1.1 - Minor Title
 *       08/11/1980 12:34  1.1.1  patch comment
 *          ada96f3 commit msg
 *          2eb6fae commit msg
 *       08/11/1980 03:56  1.1.0  initial release'
 *          2eb6fae commit msg
 *
 *    Version 1.0 - Minor Title
 *       08/11/1980 03:56  1.0.0  initial release'
 *          2eb6fae commit msg
 *
 *
 *  VERSION 0  BETA
 *  ===============
 *
 *    Version 0.9 - Minor Title
 *       08/11/1980 12:34  0.9.1  patch comment
 *          ada96f3 commit msg
 *          2eb6fae commit msg
 *       08/11/1980 03:56  0.9.0  initial release'
 *          2eb6fae commit msg
 */
class SemanticFormatter implements Formatter
{
    public function updateExistingLines(
        array $lines,
        string $version,
        ?ReleaseType $releaseType,
        ?string $comment,
        array $extraLines = []
    ): array {
        if (null === $releaseType) {
            throw new InvalidArgumentException('Option [type] in mandatory');
        }

        // Specific case for new Changelog file. We always have to write down a major
        if ([] === $lines) {
            $releaseType = ReleaseType::MAJOR;
        }

        // Insert the new lines
        array_splice($lines, $this->findPositionToInsert($lines, $releaseType), 0, $this->getNewLines($releaseType, $version, $comment));

        // Insert extra lines (like commits details)
        if ([] !== $extraLines) {
            $extraLines = $this->formatLines($extraLines);
            array_splice($lines, $this->findPositionToInsert($lines, ReleaseType::PATCH) + 1, 0, $extraLines);
        }

        return $lines;
    }

    public function getLastVersionRegex(): string
    {
        return '#\s+\d+/\d+/\d+\s\d+:\d+\s+([^\s]+)#';
    }

    /**
     * @param string[] $lines
     *
     * @return string[]
     */
    protected function formatLines(array $lines): array
    {
        foreach ($lines as $pos => $line) {
            $lines[$pos] = '         '.$line;
        }

        return $lines;
    }

    /**
     * @return string[]
     */
    protected function getNewLines(ReleaseType $type, string $version, ?string $comment): array
    {
        [$major, $minor, $patch] = explode('.', $version);
        if (ReleaseType::MAJOR === $type) {
            $title = sprintf('version %s  %s', $major, $comment);

            return array_merge(
                [
                    '',
                    strtoupper($title),
                    str_pad('', \strlen($title), '='),
                ],
                $this->getNewLines(ReleaseType::MINOR, $version, $comment)
            );
        }

        if (ReleaseType::MINOR === $type) {
            return array_merge(
                [
                    '',
                    sprintf('   Version %s.%s - %s', $major, $minor, $comment),
                ],
                $this->getNewLines(ReleaseType::PATCH, $version, 'initial release')
            );
        }

        // patch
        $date = $this->getFormattedDate();

        return [
            sprintf('      %s  %s  %s', $date, $version, $comment),
        ];
    }

    /**
     * @param string[] $lines
     *
     * @throws RelazyException
     */
    protected function findPositionToInsert(array $lines, ReleaseType $type): int
    {
        // Major are always inserted at the top
        if (ReleaseType::MAJOR === $type) {
            return 0;
        }

        // Minor must be inserted one line above the first major section
        if (ReleaseType::MINOR === $type) {
            foreach ($lines as $pos => $line) {
                if (str_starts_with($line, '=======')) {
                    return (int) $pos + 1;
                }
            }
        }

        // Patch should go directly after the first minor
        if (ReleaseType::PATCH === $type) {
            foreach ($lines as $pos => $line) {
                if (1 === preg_match('/Version\s\d+\.\d+\s\-/', $line)) {
                    return (int) $pos + 1;
                }
            }
        }

        throw new RelazyException('Invalid changelog formatting');
    }

    protected function getFormattedDate(): string
    {
        return date('d/m/Y H:i');
    }
}
