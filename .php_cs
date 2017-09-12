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
        'array_syntax' => ['syntax' => 'long'],
        'combine_consecutive_unsets' => true,
	'linebreak_after_opening_tag' => true,
        'header_comment' => ['header' => $header],
        'list_syntax' => ['syntax' => 'long'],
        'no_short_echo_tag' => true,
	'ordered_imports' => true,
	'php_unit_construct' => true,
	'php_unit_dedicate_assert' => true,
        'phpdoc_order' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name("*.php")
            ->name("*.twig")
            ->exclude('vendor/var')
            ->in(__DIR__)
    )
;
