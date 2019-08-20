<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$header = <<<HEADER
This file is part of the `liip/LiipImagineBundle` project.

(c) https://github.com/liip/LiipImagineBundle/graphs/contributors

For the full copyright and license information, please view the LICENSE.md
file that was distributed with this source code.
HEADER;

return Config::create()
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder((new Finder())->in(__DIR__))
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit57Migration:risky' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'native_constant_invocation' => [
            'fix_built_in' => false,
        ],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'escape_implicit_backslashes' => true,
        'explicit_indirect_variable' => true,
        'final_internal_class' => true,
        'fopen_flags' => [
            'b_mode' => true
        ],
        'header_comment' => [
            'header' => $header,
            'separate' => 'both'
        ],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'mb_str_functions' => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'no_php4_constructor' => true,
        'no_short_echo_tag' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private'
            ],
        ],
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'php_unit_no_expectation_annotation' => true,
        'php_unit_test_class_requires_covers' => true,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'psr4' => true,
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ]);
