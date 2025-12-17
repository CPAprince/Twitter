<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = new Finder()
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/config',
        __DIR__.'/bin',
        __DIR__.'/tools',
    ])
    ->notName('reference.php')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return new Config()
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__.'/.phpunit.result.cache')
    ->setRules([
        '@Symfony' => true,
        'strict_param' => true,
        'psr_autoloading' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder);
