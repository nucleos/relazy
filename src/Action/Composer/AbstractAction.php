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

use JsonException;
use Nucleos\Relazy\Action\BaseAction;
use Nucleos\Relazy\Exception\CommandException;

abstract class AbstractAction extends BaseAction
{
    protected const COMPOSER_JSON_FILE = 'composer.json';

    /**
     * @return mixed[]
     *
     * @throws JsonException
     * @throws CommandException
     */
    protected function getContent(): array
    {
        $content = $this->getRawContent();

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws CommandException
     */
    protected function getRawContent(): string
    {
        if (!file_exists(self::COMPOSER_JSON_FILE)) {
            throw new CommandException(sprintf('The %s file could not be loaded', self::COMPOSER_JSON_FILE));
        }

        if (!is_readable(self::COMPOSER_JSON_FILE)) {
            throw new CommandException(sprintf('Cannot read %s. Maybe you don\'t have permissions.', self::COMPOSER_JSON_FILE));
        }

        $content = file_get_contents(self::COMPOSER_JSON_FILE);

        if (false === $content) {
            throw new CommandException(sprintf('The %s file could not be loaded', self::COMPOSER_JSON_FILE));
        }

        return $content;
    }
}
