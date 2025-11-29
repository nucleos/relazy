<?php

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy;

use Nucleos\Relazy\Command\ChangesCommand;
use Nucleos\Relazy\Command\CurrentCommand;
use Nucleos\Relazy\Command\ReleaseCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

final class Application extends BaseApplication
{
    private const RELAZY_VERSION = '0.0.0';

    public function __construct()
    {
        parent::__construct('relazy - The lazy release tool', self::RELAZY_VERSION);

        $this->addCommand(new ReleaseCommand());
        $this->addCommand(new CurrentCommand());
        $this->addCommand(new ChangesCommand());
    }

    public function add(callable|Command $command): Command
    {
        $command = parent::addCommand($command);

        \assert(null !== $command);

        $command->setApplication($this);

        return $command;
    }
}
