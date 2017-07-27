<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Component\Console\Style;

/**
 * @internal
 */
interface ImagineStyleInterface
{
    public function text(string $string, array $replacements = []): self;

    public function line(string $string, array $replacements = []): self;

    public function newline(int $count = 1): self;

    public function space(int $count = 1): self;

    public function separator(string $character = null, int $width = null, string $fg = null, bool $newline = true): self;

    public function status(string $status, string $fg = null, string $bg = null): self;

    public function group(string $item, string $group, string $fg = null, string $bg = null): self;

    public function title(string $title, string $type = null, string $fg = null, string $bg = null): self;

    public function smallBlock(string $string, string $type, string $fg = null, string $bg = null, string $prefix = null): self;

    public function largeBlock(string $string, string $type, string $fg = null, string $bg = null, string $prefix = null): self;

    public function okayBlock(string $string, array $replacements = []): self;

    public function noteBlock(string $string, array $replacements = []): self;

    public function critBlock(string $string, array $replacements = []): self;
}
