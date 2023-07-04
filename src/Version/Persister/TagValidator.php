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

final class TagValidator
{
    public string $regex;

    public string $tagPrefix;

    public function __construct(string $regex, string $tagPrefix = '')
    {
        $this->regex     = $regex;
        $this->tagPrefix = $tagPrefix;
    }

    public function isValid(string $tag): bool
    {
        if ('' === $this->tagPrefix) {
            return 1 === preg_match('/^'.$this->regex.'$/', substr($tag, \strlen($this->tagPrefix)));
        }

        if (str_starts_with($tag, $this->tagPrefix)) {
            return 1 === preg_match('/^'.$this->regex.'$/', substr($tag, \strlen($this->tagPrefix)));
        }

        return false;
    }
}
