<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Changelog;

use Nucleos\Relazy\Changelog\Formatter\Formatter;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Exception\NoReleaseFoundException;
use Nucleos\Relazy\Exception\RelazyException;
use Nucleos\Relazy\Version\ReleaseType;

/**
 * Class to read/write the changelog file.
 */
final class ChangelogManager
{
    private readonly string $filePath;

    private readonly Formatter $formatter;

    public function __construct(string $filePath, Formatter $formatter)
    {
        if (!file_exists($filePath)) {
            touch($filePath);
        }

        if (!is_file($filePath) || !is_writable($filePath)) {
            throw new RelazyException(sprintf('Unable to write file [%s]', $filePath));
        }

        $this->filePath  = $filePath;
        $this->formatter = $formatter;
    }

    /**
     * @param string[] $extraLines
     */
    public function update(string $version, ?string $comment, ?ReleaseType $releaseType, array $extraLines = []): void
    {
        $lines = $this->getLines();

        $lines = $this->formatter->updateExistingLines(
            $lines,
            $version,
            $releaseType,
            $comment,
            $extraLines
        );

        file_put_contents($this->filePath, implode("\n", $lines));
    }

    public function getCurrentVersion(): string
    {
        $changelog    = $this->getRawContent();
        $versionRegex = $this->formatter->getLastVersionRegex();
        $result       = preg_match($versionRegex, $changelog, $match);

        if (1 === $result) {
            return $match[1];
        }

        throw new NoReleaseFoundException(
            'There is a format error in the CHANGELOG file, impossible to read the last version number'
        );
    }

    /**
     * @return string[]
     */
    private function getLines(): array
    {
        $lines = file($this->filePath, FILE_IGNORE_NEW_LINES);

        if (false === $lines) {
            throw new CommandException(sprintf('The %s file could not be loaded', $this->filePath));
        }

        return $lines;
    }

    /**
     * @throws CommandException
     */
    private function getRawContent(): string
    {
        $content = file_get_contents($this->filePath);

        if (false === $content) {
            throw new CommandException(sprintf('The %s file could not be loaded', $this->filePath));
        }

        return $content;
    }
}
