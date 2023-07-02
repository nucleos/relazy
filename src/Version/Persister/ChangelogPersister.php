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

use Nucleos\Relazy\Changelog\ChangelogManager;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Interaction\InteractionRequest;
use Nucleos\Relazy\Interaction\InteractionRequestAware;
use Nucleos\Relazy\Interaction\InteractionType;
use Nucleos\Relazy\Version\ReleaseType;

final class ChangelogPersister implements Persister, InteractionRequestAware
{
    private const COMMENT = 'comment';

    private const TYPE = 'type';

    private readonly string $location;

    public function __construct(?string $location = null)
    {
        $this->location = $location ?? 'CHANGELOG';
    }

    public function getCurrentVersion(Context $context): string
    {
        return $this->getChangelogManager($context)->getCurrentVersion();
    }

    public function save(string $versionNumber, Context $context): string
    {
        $comment = $context->getInformationCollection()->getValue(self::COMMENT);
        $type    = ReleaseType::tryFrom($context->getInformationCollection()->getValue(self::TYPE, null));

        $this
            ->getChangelogManager($context)
            ->update($versionNumber, $comment, $type)
        ;

        return $versionNumber;
    }

    public function getInteractionRequest(): array
    {
        return [
            new InteractionRequest(self::COMMENT, InteractionType::TEXT, [
                'description' => 'Comment associated with the release',
                'optional'    => true,
            ]),
        ];
    }

    public function getCurrentVersionTag(Context $context): string
    {
        return $this->getCurrentVersion($context);
    }

    public function getTagFromVersion(string $versionName, Context $context): string
    {
        return $versionName;
    }

    private function getChangelogManager(Context $context): ChangelogManager
    {
        return new ChangelogManager(
            $context->getProjectRoot().'/'.$this->location,
            $context->formatter
        );
    }
}
