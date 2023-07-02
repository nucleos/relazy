relazy
======

[![Latest Stable Version](https://poser.pugx.org/nucleos/relazy/v/stable)](https://packagist.org/packages/nucleos/relazy)
[![Latest Unstable Version](https://poser.pugx.org/nucleos/relazy/v/unstable)](https://packagist.org/packages/nucleos/relazy)
[![License](https://poser.pugx.org/nucleos/relazy/license)](LICENSE.md)

[![Total Downloads](https://poser.pugx.org/nucleos/relazy/downloads)](https://packagist.org/packages/nucleos/relazy)
[![Monthly Downloads](https://poser.pugx.org/nucleos/relazy/d/monthly)](https://packagist.org/packages/nucleos/relazy)
[![Daily Downloads](https://poser.pugx.org/nucleos/relazy/d/daily)](https://packagist.org/packages/nucleos/relazy)

[![Continuous Integration](https://github.com/nucleos/relazy/workflows/Continuous%20Integration/badge.svg?event=push)](https://github.com/nucleos/relazy/actions?query=workflow%3A"Continuous+Integration"+event%3Apush)
[![Code Coverage](https://codecov.io/gh/nucleos/relazy/graph/badge.svg)](https://codecov.io/gh/nucleos/relazy)
[![Type Coverage](https://shepherd.dev/github/nucleos/relazy/coverage.svg)](https://shepherd.dev/github/nucleos/relazy)

Relazy is a handy tool to help releasing new versions of your software. You can define the type
of version generator you want to use (e.g. semantic versioning), where you want to store
the version (e.g. in a changelog file or as a VCS tag) and a list of actions that should be
executed before or after the release of a new version.

This project was originally inspired by the [RMT](https://github.com/liip/RMT) project, but with some major refactoring
and more modern approach (e.g. PHP config with autocompletion).

Installation
------------

### Option 1: As a development dependency of your project

In order to use relazy in your project you should use [Composer](https://getcomposer.org/) to install it
as a dev-dependency. Just go to your project's root directory and execute:

```
composer require --dev nucleos/relazy
```

Then you must need to create a `.relazy.php` config file to run the `relazy` executable script in your project's
root folder.

```
./relazy
```

Once there, your best option is to pick one of the [configuration examples](#configuration-examples) below
and adapt it to your needs.

### Option 2: As a global Composer installation

You can add relazy to your global `composer.json` and have it available globally for all your projects. Therefor
just run the following command:

```
composer global require nucleos/relazy
```

Make sure you have `~/.composer/vendor/bin/` in your $PATH.

### Option 3: As a Phar file

Relazy can be installed through [phar-composer](https://github.com/clue/phar-composer/), which needs to
be [installed](https://github.com/clue/phar-composer/#install) therefor. This useful tool allows you to create runnable
Phar files from Composer packages.

If you have phar-composer installed, you can run:

```
phar-composer install nucleos/relazy
```

and have phar-composer build and install the Phar file to your `$PATH`, which then allows you to run it simply as `relazy`
from the command line, or you can run

```
phar-composer build nucleos/relazy
```

and copy the resulting Phar file manually to where you need it. Either make the Phar file executable
via `chmod +x relazy.phar` and execute it directly `./relazy.phar` or run it by invoking it through PHP
via `php relazy.phar`.

For the usage substitute relazy with whatever variant you have decided to use.

Usage
-----
Using relazy is very straightforward, just run the command:

```
./relazy release
```

Relazy will then execute the following tasks:

1. Execute the startup actions
2. Ask the user to answer potentials questions
3. Execute the pre-release actions
4. Release
    * Generate a new version number
    * Persist the new version number
5. Execute the post-release actions

Here is an example output:

### Additional commands

The `release` command provides the main behavior of the tool, additional some extra commands are available:

* `current` will show your project current version number (alias version)
* `changes` display the changes that will be incorporated in the next release

Configuration
-------------

All relazy configurations have to be done in `.relazy.php`. The file is divided in the following elements:

* `vcs`: The type of VCS you are using, can be `Git` or `Noop`
* `startupActions`: A list `[]` of actions that will be executed just after startup without user interaction
* `preReleaseActions`: A list `[]` of actions that will be executed before the release process
* `versionGenerator`: The generator to use to create a new version (mandatory)
* `versionPersister`: The persister to use to store the versions (mandatory)
* `postReleaseActions`: A list `[]` of actions that will be executed after the release

All entries of this config work the same. You have to specify the class you want to handle the action. Example:

```php
use Nucleos\Relazy\Changelog\Formatter\SemanticFormatter;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Version\Generator\SimpleGenerator;
use Nucleos\Relazy\Version\Persister\TagPersister;
use Nucleos\Relazy\VersionControl\Git;

return (new RelazyConfig(new Git()))
    ->versionGenerator(new SimpleGenerator())
    ->versionPersister(new TagPersister(tagPrefix: 'v_'))
    ->formatter(new SemanticFormatter())
    // ...
;
```

Extend it
---------

Relazy is providing some existing actions, generators, and persisters. If needed you can add your own by creating a PHP
script in your project, and referencing it in the configuration:

```php
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\VersionControl\Git;

return (new RelazyConfig(new Git()))
    ->versionGenerator(new \Acme\CustomGenerator())
    // ...
;
```

Configuration examples
----------------------
Most of the time, it will be easier for you to pick up an example below and adapt it to your needs.

### No VCS, changelog updater only

```php
use Nucleos\Relazy\Changelog\Formatter\SemanticFormatter;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Version\Generator\SimpleGenerator;
use Nucleos\Relazy\Version\Persister\ChangelogPersister;
use Nucleos\Relazy\VersionControl\Noop;

return (new RelazyConfig())
    ->versionGenerator(new SimpleGenerator())
    ->versionPersister(new ChangelogPersister())
    ->formatter(new SemanticFormatter())
    // ...
;
```

### Using Git tags, simple versioning and startup actions

```php
new Nucleos\Relazy\Action\VersionControl\CheckWorkingCopyAction;
new Nucleos\Relazy\Action\VersionControl\LastChangesAction;
use Nucleos\Relazy\Changelog\Formatter\SemanticFormatter;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Version\Generator\SimpleGenerator;
use Nucleos\Relazy\Version\Persister\ChangelogPersister;
use Nucleos\Relazy\VersionControl\Git;

return (new RelazyConfig(new Git()))
    ->versionGenerator(new SimpleGenerator())
    ->versionPersister(new ChangelogPersister())
    ->formatter(new SemanticFormatter())
    ->startupActions([
        new CheckWorkingCopyAction(),
        new DisplayLastChanges(),
    ])
;
```

### Using Git tags, simple versioning and startup actions

```php
use Nucleos\Relazy\Action\Composer\ValidateAction;
use Nucleos\Relazy\Action\Composer\StabilityCheckAction;
use Nucleos\Relazy\Action\Composer\DependencyStabilityCheckAction;
use Nucleos\Relazy\Changelog\Formatter\SemanticFormatter;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Version\Generator\SimpleGenerator;
use Nucleos\Relazy\Version\Persister\TagPersister;
use Nucleos\Relazy\VersionControl\Git;

return (new RelazyConfig(new Git(signCommit: true, signTag: true)))
    ->versionGenerator(new SimpleGenerator())
    ->versionPersister(new TagPersister())
    ->startupActions([
        new ValidateAction(),
        new StabilityCheckAction(),
        new DependencyStabilityCheckAction(allowList: [
            'symfony/console',
            'phpunit/phpunit' => 'require-dev',
        ]),
    ])
;
```

### Using Git tags, simple versioning and startup actions, and gpg sign commit and tags

```php
new Nucleos\Relazy\Action\VersionControl\CheckWorkingCopyAction;
new Nucleos\Relazy\Action\VersionControl\LastChangesAction;
use Nucleos\Relazy\Changelog\Formatter\SemanticFormatter;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Version\Generator\SimpleGenerator;
use Nucleos\Relazy\Version\Persister\ChangelogPersister;
use Nucleos\Relazy\VersionControl\Git;

return (new RelazyConfig(new Git(signCommit: true, signTag: true)))
    ->versionGenerator(new SimpleGenerator())
    ->versionPersister(new ChangelogPersister())
    ->startupActions([
        new CheckWorkingCopyAction(),
        new DisplayLastChanges(),
    ])
;
```

### Using Git tags with prefix, semantic versioning, updating two files and pushing automatically

```php
use Nucleos\Relazy\Action\Filesystem\FilesUpdateAction;
use Nucleos\Relazy\Action\VersionControl\PublishAction;
use Nucleos\Relazy\Config\RelazyConfig;
use Nucleos\Relazy\Version\Generator\SemanticGenerator;
use Nucleos\Relazy\Version\Persister\TagPersister;
use Nucleos\Relazy\VersionControl\Git;

return (new RelazyConfig(new Git(signCommit: true, signTag: true)))
    ->versionGenerator(new SemanticGenerator())
    ->versionPersister(new TagPersister(tagPrefix: 'v_'))
    ->preReleaseActions([
        new FilesUpdateAction(files: [
            'config.yml' => '%version%',
            'app.ini' => 'dynamic-version: %version%'
        ]),
    ])
    ->postReleaseActions([
        new PublishAction(),
    ])
;
```

