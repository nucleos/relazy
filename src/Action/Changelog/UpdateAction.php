<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\Changelog;

use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Changelog\ChangelogManager;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Exception\NoReleaseFoundException;
use Nucleos\Relazy\Interaction\InteractionRequest;
use Nucleos\Relazy\Interaction\InteractionRequestAware;
use Nucleos\Relazy\Interaction\InteractionType;
use Nucleos\Relazy\Output\Console;
use Nucleos\Relazy\Version\ReleaseType;

final class UpdateAction extends BaseAction implements InteractionRequestAware
{
    private const COMMENT = 'comment';

    private const TYPE = 'type';

    private readonly bool $dumpCommits;

    private readonly bool $excludeMergeCommits;

    private readonly string $file;

    public function __construct(
        ?bool $dumpCommits = null,
        ?bool $excludeMergeCommits = null,
        ?string $file = null
    ) {
        $this->dumpCommits         = $dumpCommits         ?? true;
        $this->excludeMergeCommits = $excludeMergeCommits ?? true;
        $this->file                = $file                ?? 'CHANGELOG';
    }

    public function execute(Context $context, Console $console): void
    {
        $extraLines = [];

        if (true === $this->dumpCommits) {
            try {
                $extraLines = $context->getVersionControl()->getAllModificationsSince(
                    $context->versionPersister->getCurrentVersionTag($context),
                    false,
                    $this->excludeMergeCommits
                );
            } catch (NoReleaseFoundException) {
                $console->writeWarning('No commits dumped as this is the first release');

                return;
            }
        }

        if ($context->isDryRun()) {
            $console->writeWarning(sprintf('Skipping Changelog generation for "%s" file', $this->file));

            return;
        }

        $nextVersion = $context->getNextVersion();

        if (null === $nextVersion) {
            throw CommandOrderException::forField('next version');
        }

        $manager = new ChangelogManager($this->file, $context->formatter);
        $manager->update(
            $nextVersion,
            $context->getInformationCollection()->getValue(self::COMMENT),
            ReleaseType::tryFrom($context->getInformationCollection()->getValue(self::TYPE, null)),
            $extraLines
        );
    }

    public function getInteractionRequest(): array
    {
        return [
            new InteractionRequest(self::COMMENT, InteractionType::TEXT, [
                'description' => 'Comment for release',
                'optional'    => true,
            ]),
        ];
    }
}
