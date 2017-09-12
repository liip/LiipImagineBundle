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
        //'@PHP56Migration' => true,
        '@Symfony' => true,
        //'@Symfony:risky' => true,
        //'align_multiline_comment' => true,
        'array_syntax' => ['syntax' => 'long'],
        //'blank_line_before_statement' => true,
        'combine_consecutive_unsets' => true,
	'linebreak_after_opening_tag' => true,
        // one should use PHPUnit methods to set up expected exception instead of annotations
        //'general_phpdoc_annotation_remove' => ['annotations' => ['expectedException', 'expectedExceptionMessage', 'expectedExceptionMessageRegExp']],
        'header_comment' => ['header' => $header],
        //'heredoc_to_nowdoc' => true,
        'list_syntax' => ['syntax' => 'long'],
        //'method_argument_space' => ['ensure_fully_multiline' => true],
        //'no_extra_consecutive_blank_lines' => ['tokens' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']],
        //'no_null_property_initialization' => true,
        'no_short_echo_tag' => true,
        //'no_superfluous_elseif' => true,
        //'no_unneeded_curly_braces' => true,
        //'no_unneeded_final_method' => true,
        //'no_unreachable_default_argument_value' => true,
        //'no_useless_else' => true,
        //'no_useless_return' => true,
        //'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_construct' => true,
	'php_unit_dedicate_assert' => true,
	//'php_unit_strict' => true,
        //'php_unit_test_class_requires_covers' => true,
        //'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        //'phpdoc_types_order' => true,
        //'semicolon_after_instruction' => true,
        //'single_line_comment_style' => true,
        //'strict_comparison' => true,
        //'strict_param' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name("*.php")
            ->name("*.twig")
            ->exclude('vendor/var')
            ->in(__DIR__)
    )
;
