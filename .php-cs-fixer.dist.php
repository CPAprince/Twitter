<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/config',
        __DIR__ . '/bin',
        __DIR__ . '/tools',
    ])
    ->exclude([
        'var',
        'vendor',
        'public',
        '.github',
        'frankenphp',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
    ->setRules([
        '@PSR12'   => true,
        '@Symfony' => true,

        'strict_param'    => true,
        'psr_autoloading' => true,
        'declare_strict_types' => true,

        'array_indentation' => true,
        'array_syntax'      => ['syntax' => 'short'],
        'list_syntax'       => ['syntax' => 'short'],

        'cast_spaces'  => ['space' => 'none'],


        'declare_equal_normalize' => ['space' => 'single'],
        'increment_style'         => ['style' => 'post'],

        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_only',
        ],
        'phpdoc_order' => true,
        'phpdoc_align' => false,

        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],

        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order'  => ['class', 'const', 'function'],
        ],

        'yoda_style' => false,
    ])
    ->setFinder($finder);
