<?php

$header = <<<EOF
This file is part of Sulu.

(c) Sulu GmbH

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude(['var/cache', 'tests/Resources/cache', 'node_modules'])
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'concat_space' => ['spacing' => 'one'],
        'class_definition' => false,
        'declare_strict_types' => true,
        'function_declaration' => ['closure_function_spacing' => 'none'],
        'header_comment' => ['header' => $header],
        'native_function_invocation' => ['include' => ['@internal']],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'remove_inheritdoc' => true],
        'no_useless_return' => true,
        'no_useless_else' => true,
        'php_unit_strict' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_types_order' => false,
        'strict_comparison' => true,
        'single_line_throw' => false,
        'strict_param' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->exclude('cache')
            ->exclude('Tests/reports/')
            ->in(__DIR__)
    );

return $config;
