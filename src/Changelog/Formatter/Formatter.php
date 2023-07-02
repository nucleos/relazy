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

interface Formatter
{
    /**
     * @param array<array-key, string> $lines
     * @param array<array-key, string> $extraLines
     *
     * @return array<array-key, string>
     */
    public function updateExistingLines(
        array $lines,
        string $version,
        ?ReleaseType $releaseType,
        ?string $comment,
        array $extraLines = []
    ): array;

    public function getLastVersionRegex(): string;
}
