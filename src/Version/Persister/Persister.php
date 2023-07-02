<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Version\Persister;

use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\NoReleaseFoundException;

interface Persister
{
    /**
     * Return the current release name.
     *
     * @throws NoReleaseFoundException
     */
    public function getCurrentVersion(Context $context): string;

    public function save(string $versionNumber, Context $context): string;

    public function getCurrentVersionTag(Context $context): string;

    public function getTagFromVersion(string $versionName, Context $context): string;
}
