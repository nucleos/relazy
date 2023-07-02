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

interface Generator
{
    public function generateNextVersion(Context $context): string;

    /**
     * Function used to compare two versions. Must return:
     *  * -1 if $a is older than $b
     *  * 0 if $a and $b are the same
     *  * 1 if $a is more recent than $b.
     */
    public function compareVersions(string $a, string $b): int;

    public function getInitialVersion(): string;

    public function getValidationRegex(): string;
}
