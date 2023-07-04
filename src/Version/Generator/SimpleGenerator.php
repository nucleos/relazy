<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Version\Generator;

use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\RelazyException;

final class SimpleGenerator
{
    public function generateNextVersion(Context $context): string
    {
        $currentVersion = $context->getCurrentVersion();

        if (!is_numeric($currentVersion)) {
            throw new RelazyException(
                sprintf('Current version format is invalid (%s). It should be numeric', $currentVersion)
            );
        }

        $numericVersion = (int) $currentVersion;

        return (string) ++$numericVersion;
    }

    public function getInitialVersion(): string
    {
        return '0';
    }

    public function compareVersions(string $a, string $b): int
    {
        return $a <=> $b;
    }

    public function getValidationRegex(): string
    {
        return '\d+';
    }
}
