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
 * Adding the version heading at the top of the CHANGELOG file.
 *
 * This is useful when you constantly record relevant changes and want a new
 * heading so that people see in which version that changed.
 *
 * An example file:
 *
 * Changelog
 * =========
 *
 * * **2013-11-01**: A changelog entry for a feature that is in no released
 *   version yet. The version header will be added right before this when
 *   the addTop formatter is used.
 *
 * 1.0.0-RC3
 * ---------
 * * **2013-10-04**: A manual changelog entry
 * * **2013-10-02**: A first entry into the changlog
 *
 * 1.0.0-beta-3
 * ------------
 * * **2013-09-23**: An older changelog entry
 */
final class AddTopFormatter implements Formatter
{
    /** @psalm-suppress InvalidReturnType */
    public function updateExistingLines(
        array $lines,
        string $version,
        ?ReleaseType $releaseType,
        ?string $comment,
        array $extraLines = []
    ): array {
        $pos = 0;

        if (null === $comment) {
            array_splice($lines, $pos, 0, [$comment, '']);
        }

        if ([] !== $extraLines) {
            array_splice($lines, $pos, 0, $extraLines);
        }

        array_splice($lines, $pos, 0, [$version, str_repeat('-', \strlen($version)), '']);

        /**
         * @psalm-suppress InvalidReturnStatement
         *
         * @phpstan-ignore-next-line
         */
        return $lines;
    }

    public function getLastVersionRegex(): string
    {
        return '#.*#';
    }
}
