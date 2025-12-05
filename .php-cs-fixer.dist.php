<?php

$finder = new PhpCsFixer\Finder()
    ->in([
        __DIR__.'/bin',
        __DIR__.'/src',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return new PhpCsFixer\Config()
    ->setRules([
        '@Symfony' => true,
        'strict_comparison' => true,
        'declare_strict_types' => ['preserve_existing_declaration' => false],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
