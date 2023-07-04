<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Nucleos\Relazy\Action\Changelog\UpdateAction;
use Nucleos\Relazy\Action\Composer\OutdatedAction;
use Nucleos\Relazy\Action\Composer\StabilityCheckAction;
use Nucleos\Relazy\Action\Composer\ValidateAction;
use Nucleos\Relazy\Action\VersionControl\CommitAction;
use Nucleos\Relazy\Action\VersionControl\CurrentVersionAction;
use Nucleos\Relazy\Action\VersionControl\LastChangesAction;
use Nucleos\Relazy\Changelog\Formatter\PrefixGroupFormatter;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Version\Generator\SemanticGenerator;
use Nucleos\Relazy\Version\Persister\TagPersister;
use Nucleos\Relazy\VersionControl\Git;

return (new RelazyConfig(new Git()))
    ->versionGenerator(new SemanticGenerator())
    ->versionPersister(new TagPersister())
    ->formatter(new PrefixGroupFormatter(
        defaultGroup: 'Changed',
        groups: ['Feature', 'Enhancement', 'Fixed', 'Translation', 'Changed'],
        ignorePrefixes: ['Doc', 'Pedantic']
    ))
    ->startupActions([
        new ValidateAction(),
        new StabilityCheckAction(),
        new OutdatedAction(),
        new LastChangesAction(),
        new CurrentVersionAction(),
    ])
    ->preReleaseActions([
        new UpdateAction(
            file: 'CHANGELOG.MD',
        ),
        new CommitAction(filter: ['CHANGELOG.MD']),
    ])
;
