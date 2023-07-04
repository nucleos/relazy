<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\Composer;

use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Output\Console;

final class StabilityCheckAction extends AbstractAction
{
    private readonly string $stability;

    public function __construct(?string $stability = null)
    {
        $this->stability = $stability ?? 'stable';
    }

    public function execute(Context $context, Console $console): void
    {
        $contents = $this->getContent();

        if (!isset($contents['minimum-stability']) && 'stable' !== $this->stability) {
            throw new CommandException(sprintf(
                'The "minimum-stability" is not set, but relazy config requires: %s ',
                $this->stability
            ));
        }

        if (!isset($contents['minimum-stability'])) {
            return;
        }

        if ($contents['minimum-stability'] === $this->stability) {
            return;
        }

        throw new CommandException(sprintf(
            'The "minimum-stability" is set to: %s, but relazy config requires: %s ',
            $contents['minimum-stability'],
            $this->stability
        ));
    }
}
