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
use Nucleos\Relazy\Output\Console;

final class UpdateVersionAction extends AbstractAction
{
    public function execute(Context $context, Console $console): void
    {
        $newVersion   = $context->getNextVersion();
        $composerFile = $context->getProjectRoot().'/'.self::COMPOSER_JSON_FILE;

        if ($context->isDryRun()) {
            $console->writeWarning(sprintf('Skipping "%s" version update', self::COMPOSER_JSON_FILE));

            return;
        }

        $fileContent = $this->getRawContent();
        $fileContent = preg_replace('/("version":[^,]*,)/', '"version": "'.$newVersion.'",', $fileContent);

        file_put_contents($composerFile, $fileContent);
    }
}
