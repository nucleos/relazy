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
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Exception\NoReleaseFoundException;
use Nucleos\Relazy\Exception\RelazyException;

final class TagPersister implements Persister
{
    private readonly ?string $versionRegex;

    private readonly string $tagPrefix;

    public function __construct(?string $tagPattern = null, ?string $tagPrefix = null)
    {
        $this->tagPrefix    = $tagPrefix ?? '';
        $this->versionRegex = $tagPattern;
    }

    public function getCurrentVersion(Context $context): string
    {
        $versionRegex = $this->versionRegex ?? $context->getVersionRegexPattern();

        if (null === $versionRegex) {
            throw CommandOrderException::forField('version regex');
        }

        $tags = $this->getValidVersionTags($versionRegex, $context);

        if ([] === $tags) {
            throw new NoReleaseFoundException('No tag matching the regex ['.$this->getTagPrefix($context).$versionRegex.']');
        }

        // Extract versions from tags and sort them
        $versions = $this->getVersionFromTags($tags, $context);

        if ([] === $versions) {
            throw new NoReleaseFoundException('No versions found in tag list');
        }

        usort($versions, [$context->versionGenerator, 'compareVersions']);

        return array_pop($versions);
    }

    public function save(string $versionNumber, Context $context): string
    {
        $tagName = $this->getTagFromVersion($versionNumber, $context);

        $context->getVersionControl()->createTag($tagName);

        return $tagName;
    }

    public function getCurrentVersionTag(Context $context): string
    {
        return $this->getTagFromVersion($this->getCurrentVersion($context), $context);
    }

    public function getTagFromVersion(string $versionName, Context $context): string
    {
        return $this->getTagPrefix($context).$versionName;
    }

    private function getTagPrefix(Context $context): string
    {
        return $this->generatePrefix($this->tagPrefix, $context);
    }

    private function getVersionFromTag(string $tagName, Context $context): string
    {
        return substr($tagName, \strlen($this->getTagPrefix($context)));
    }

    /**
     * @param string[] $tags
     *
     * @return string[]
     */
    private function getVersionFromTags(array $tags, Context $context): array
    {
        $versions = [];
        foreach ($tags as $tag) {
            $versions[] = $this->getVersionFromTag($tag, $context);
        }

        return $versions;
    }

    /**
     * Return all tags matching the versionRegex and prefix.
     *
     * @return string[]
     */
    private function getValidVersionTags(string $versionRegex, Context $context): array
    {
        $validator = new TagValidator($versionRegex, $this->getTagPrefix($context));

        $validTags = [];
        foreach ($context->getVersionControl()->getTags() as $tag) {
            if ($validator->isValid($tag)) {
                $validTags[] = $tag;
            }
        }

        return $validTags;
    }

    private function generatePrefix(string $userTag, Context $context): string
    {
        preg_match_all('/\{([^\}]*)\}/', $userTag, $placeHolders);

        foreach ($placeHolders[1] as $pos => $placeHolder) {
            if ('branch-name' === $placeHolder) {
                $replacement = $context->getVersionControl()->getCurrentBranch();
            } elseif ('date' === $placeHolder) {
                $replacement = date('Y-m-d');
            } else {
                throw new RelazyException(sprintf('There is no rules to process the prefix placeholder [%s]', $placeHolder));
            }

            $userTag = str_replace($placeHolders[0][$pos], $replacement, $userTag);
        }

        return $userTag;
    }
}
