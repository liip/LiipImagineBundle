<?php

$header = <<<'EOF'
This file is part of the `liip/LiipImagineBundle` project.

(c) https://github.com/liip/LiipImagineBundle/graphs/contributors

For the full copyright and license information, please view the LICENSE.md
file that was distributed with this source code.
EOF;

$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'array_syntax' => array('syntax' => 'long'),
    'combine_consecutive_unsets' => true,
    'header_comment' => array('header' => $header),
    'heredoc_to_nowdoc' => true,
    'linebreak_after_opening_tag' => true,
    'list_syntax' => array('syntax' => 'long'),
    'no_short_echo_tag' => true,
    'no_unreachable_default_argument_value' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'php_unit_construct' => true,
    'php_unit_dedicate_assert' => true,
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_order' => true,
    'psr4' => true,
    'strict_comparison' => true,
    'strict_param' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name("*.php")
            ->name("*.twig")
            ->exclude('vendor/var')
            ->in(__DIR__)
    )
;
