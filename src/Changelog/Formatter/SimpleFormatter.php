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

final class SimpleFormatter implements Formatter
{
    public function updateExistingLines(
        array $lines,
        string $version,
        ?ReleaseType $releaseType,
        ?string $comment,
        array $extraLines = []
    ): array {
        $date = $this->getFormattedDate();
        array_splice($lines, 0, 0, [sprintf('%s  %s  %s', $date, $version, $comment)]);

        if ([] !== $extraLines) {
            array_splice($lines, 1, 0, $extraLines);
        }

        return $lines;
    }

    public function getLastVersionRegex(): string
    {
        return '#\d+/\d+/\d+\s\d+:\d+\s\s([^\s]+)#';
    }

    private function getFormattedDate(): string
    {
        return date('d/m/Y H:i');
    }
}
