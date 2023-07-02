<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\VersionControl;

use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Interaction\InteractionRequest;
use Nucleos\Relazy\Interaction\InteractionRequestAware;
use Nucleos\Relazy\Interaction\InteractionType;
use Nucleos\Relazy\Output\Console;

final class PublishAction extends BaseAction implements InteractionRequestAware
{
    private const AUTO_PUBLISH_OPTION = 'auto-publish';

    private const REMOTE = 'remote';

    private readonly bool $askConfirmation;

    private readonly bool $askRemoteName;

    private readonly ?string $remoteName;

    public function __construct(
        ?string $remoteName = null,
        ?bool $askConfirmation = null,
        ?bool $askRemoteName = null
    ) {
        $this->remoteName      = $remoteName;
        $this->askConfirmation = $askConfirmation ?? true;
        $this->askRemoteName   = $askRemoteName   ?? false;
    }

    public function execute(Context $context, Console $console): void
    {
        if ($this->askConfirmation) {
            $informationCollection = $context->getInformationCollection();

            if (!$informationCollection->hasValue(self::AUTO_PUBLISH_OPTION)) {
                $answer = $console->confirm('Do you want to publish your release (default: <green>y</green>):');

                $informationCollection->setValue(self::AUTO_PUBLISH_OPTION, true === $answer ? 'y' : 'n');
            }

            // Skip if the user didn't ask for publishing
            if ('y' !== $informationCollection->getValue(self::AUTO_PUBLISH_OPTION)) {
                $console->writeError('requested to be ignored');

                return;
            }
        }

        if ($context->isDryRun()) {
            $console->writeWarning('Skipping VCS publish');

            return;
        }

        $nextVersion = $context->getNextVersion();

        if (null === $nextVersion) {
            throw CommandOrderException::forField('next version');
        }

        $context->getVersionControl()->publishChanges($this->getRemote($context));
        $context->getVersionControl()->publishTag(
            $context->versionPersister->getTagFromVersion(
                $nextVersion,
                $context
            ),
            $this->getRemote($context)
        );
    }

    public function getInteractionRequest(): array
    {
        $list = [];

        if ($this->askConfirmation) {
            $list[] = new InteractionRequest(self::AUTO_PUBLISH_OPTION, InteractionType::YES_NO, [
                'description' => 'Changes will be published automatically',
                'interactive' => false,
            ]);
        }

        if ($this->askRemoteName) {
            $list[] = new InteractionRequest(self::REMOTE, InteractionType::TEXT, [
                'description' => 'Remote to push changes',
                'default'     => 'origin',
            ]);
        }

        return $list;
    }

    private function getRemote(Context $context): ?string
    {
        if ($this->askRemoteName) {
            return $context->getInformationCollection()->getValue(self::REMOTE);
        }

        return $this->remoteName ?? null;
    }
}
