<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (extension_loaded('phar') && method_exists('Phar', 'running') && file_exists($file = Phar::running().'/vendor/autoload.php')) {
    $loader = require_once $file;
} elseif (file_exists($file = __DIR__.'/../../autoload.php')) {
    // Composer standard location
    $loader = require_once $file;
    $loader->add('Nucleos\Relazy\Tests', __DIR__.'/test');
    $loader->add('Nucleos', __DIR__.'/src');
} elseif (file_exists($file = __DIR__.'/vendor/autoload.php')) {
    // Composer when on relazy standalone install (used in travis.ci)
    $loader = require $file;
    $loader->add('Nucleos\Relazy\Tests', __DIR__.'/test');
    $loader->add('Nucleos', __DIR__.'/src');
} else {
    throw new Exception('Unable to find the an autoloader');
}
