<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'strict_comparison' => true,
        'declare_strict_types' => ['preserve_existing_declaration' => false],
    ])
    ->setFinder($finder)
;
