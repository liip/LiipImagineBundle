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
    /**
     * @param string $string
     * @param array  $replacements
     *
     * @return ImagineStyleInterface
     */
    public function text(string $string, array $replacements = []): ImagineStyleInterface;

    /**
     * @param string $string
     * @param array  $replacements
     *
     * @return ImagineStyleInterface
     */
    public function line(string $string, array $replacements = []): ImagineStyleInterface;

    /**
     * @param int $count
     *
     * @return ImagineStyleInterface
     */
    public function newline(int $count = 1): ImagineStyleInterface;

    /**
     * @param int $count
     *
     * @return ImagineStyleInterface
     */
    public function space(int $count = 1): ImagineStyleInterface;

    /**
     * @param string|null $character
     * @param int|null    $width
     * @param string|null $fg
     * @param bool        $newline
     *
     * @return ImagineStyleInterface
     */
    public function separator(string $character = null, int $width = null, string $fg = null, bool $newline = true): ImagineStyleInterface;

    /**
     * @param string      $status
     * @param string|null $fg
     * @param string|null $bg
     *
     * @return ImagineStyleInterface
     */
    public function status(string $status, string $fg = null, string $bg = null): ImagineStyleInterface;

    /**
     * @param string      $item
     * @param string      $group
     * @param string|null $fg
     * @param string|null $bg
     *
     * @return ImagineStyleInterface
     */
    public function group(string $item, string $group, string $fg = null, string $bg = null): ImagineStyleInterface;

    /**
     * @param string      $title
     * @param string|null $type
     * @param string      $fg
     * @param string      $bg
     *
     * @return ImagineStyleInterface
     */
    public function title(string $title, string $type = null, string $fg = null, string $bg = null): ImagineStyleInterface;

    /**
     * @param string      $string
     * @param string      $type
     * @param string|null $fg
     * @param string|null $bg
     * @param string|null $prefix
     *
     * @return ImagineStyleInterface
     */
    public function smallBlock(string $string, string $type, string $fg = null, string $bg = null, string $prefix = null): ImagineStyleInterface;

    /**
     * @param string      $string
     * @param string      $type
     * @param string|null $fg
     * @param string|null $bg
     * @param string|null $prefix
     *
     * @return ImagineStyleInterface
     */
    public function largeBlock(string $string, string $type, string $fg = null, string $bg = null, string $prefix = null): ImagineStyleInterface;

    /**
     * @param string $string
     * @param array  $replacements
     *
     * @return ImagineStyleInterface
     */
    public function okayBlock(string $string, array $replacements = []): ImagineStyleInterface;

    /**
     * @param string $string
     * @param array  $replacements
     *
     * @return ImagineStyleInterface
     */
    public function noteBlock(string $string, array $replacements = []): ImagineStyleInterface;

    /**
     * @param string $string
     * @param array  $replacements
     *
     * @return ImagineStyleInterface
     */
    public function critBlock(string $string, array $replacements = []): ImagineStyleInterface;
}
