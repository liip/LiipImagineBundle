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

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;

/**
 * @internal
 */
class ImagineStyle implements ImagineStyleInterface
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var bool
     */
    private $decoration;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $decoration
     */
    public function __construct(InputInterface $input, OutputInterface $output, bool $decoration = true)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->decoration = $decoration;
    }

    /**
     * {@inheritdoc}
     */
    public function text(string $string, array $replacements = []): ImagineStyleInterface
    {
        $this->io->write($this->compileString($string, $replacements));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function line(string $string, array $replacements = []): ImagineStyleInterface
    {
        $this->io->writeln($this->compileString($string, $replacements));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function newline(int $count = 1): ImagineStyleInterface
    {
        $this->io->newLine($count);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function space(int $count = 1): ImagineStyleInterface
    {
        return $this->text(str_repeat(' ', $count));
    }

    /**
     * {@inheritdoc}
     */
    public function separator(string $character = null, int $width = null, string $fg = null, bool $newline = true): ImagineStyleInterface
    {
        if (null === $width) {
            $width = class_exists(Terminal::class) ? (new Terminal())->getWidth() : 80;
        }

        $this->text('<fg=%2$s;>%1$s</>', [str_repeat($character ?: '-', $width), $fg ?: 'default']);

        if ($newline) {
            $this->newline();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function status(string $status, string $fg = null, string $bg = null): ImagineStyleInterface
    {
        return $this->text(
            sprintf('<fg=%2$s;bg=%3$s>(</><fg=%2$s;bg=%3$s;options=bold>%1$s</><fg=%2$s;bg=%3$s>)</>', $status, $fg ?: 'default', $bg ?: 'default')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $item, string $group, string $fg = null, string $bg = null): ImagineStyleInterface
    {
        return $this->text(
            sprintf('<fg=%3$s;bg=%4$s;options=bold>%1$s[</><fg=%3$s;bg=%4$s>%2$s</><fg=%3$s;bg=%4$s;options=bold>]</>', $item, $group, $fg ?: 'default', $bg ?: 'default')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function title(string $title, string $type = null, string $fg = null, string $bg = null): ImagineStyleInterface
    {
        if (!$this->decoration) {
            return $this->plainTitle($title, $type);
        }

        return $this->block($title, $type, $fg ?: 'white', $bg ?: 'magenta');
    }

    /**
     * {@inheritdoc}
     */
    public function smallBlock(string $string, string $type, string $fg = null, string $bg = null, string $prefix = null): ImagineStyleInterface
    {
        return $this->block($string, $type, $fg, $bg, $prefix, false);
    }

    /**
     * {@inheritdoc}
     */
    public function largeBlock(string $string, string $type, string $fg = null, string $bg = null, string $prefix = null): ImagineStyleInterface
    {
        return $this->block($string, $type, $fg, $bg, $prefix, true);
    }

    /**
     * {@inheritdoc}
     */
    public function okayBlock(string $string, array $replacements = []): ImagineStyleInterface
    {
        return $this->largeBlock($this->compileString(strip_tags($string), $replacements), 'OKAY', 'black', 'green', '-');
    }

    /**
     * {@inheritdoc}
     */
    public function noteBlock(string $string, array $replacements = []): ImagineStyleInterface
    {
        return $this->largeBlock($this->compileString(strip_tags($string), $replacements), 'NOTE', 'yellow', 'black', '/');
    }

    /**
     * {@inheritdoc}
     */
    public function critBlock(string $string, array $replacements = []): ImagineStyleInterface
    {
        return $this->largeBlock($this->compileString(strip_tags($string), $replacements), 'ERROR', 'white', 'red', '#');
    }

    /**
     * @param string      $title
     * @param string|null $type
     *
     * @return ImagineStyleInterface
     */
    private function plainTitle(string $title, string $type = null): ImagineStyleInterface
    {
        $this->newline();

        if ($type) {
            $this->line('# [%s] %s', [$type, $title]);
        } else {
            $this->line('# %s', [$title]);
        }

        return $this->newline();
    }

    /**
     * @param string      $string
     * @param string|null $type
     * @param string|null $fg
     * @param string|null $bg
     * @param string|null $prefix
     * @param bool        $padding
     *
     * @return ImagineStyleInterface
     */
    private function block(string $string, string $type = null, string $fg = null, string $bg = null, string $prefix = null, bool $padding = true): ImagineStyleInterface
    {
        if (!$this->decoration) {
            return $this->plainBlock($string, $type);
        }

        $this->io->block($string, $type, sprintf('fg=%s;bg=%s', $fg ?: 'default', $bg ?: 'default'), $prefix ? sprintf(' %s ', $prefix) : ' ', $padding);

        return $this;
    }

    /**
     * @param string $string
     * @param string $type
     *
     * @return ImagineStyleInterface
     */
    private function plainBlock(string $string, string $type): ImagineStyleInterface
    {
        return $this
            ->newline()
            ->line('[%s] %s', [$type, $string])
            ->newline();
    }

    /**
     * @param string $format
     * @param array  $replacements
     *
     * @return string
     */
    private function compileString(string $format, array $replacements = []): string
    {
        if (!$this->decoration) {
            $format = strip_tags($format);
        }

        if (0 === count($replacements)) {
            return $format;
        }

        if (false !== $compiled = @vsprintf($format, $replacements)) {
            return $compiled;
        }

        throw new InvalidArgumentException(
            sprintf('Invalid string format "%s" or replacements "%s".', $format, implode(', ', array_map(function ($replacement) {
                return var_export($replacement, true);
            }, $replacements)))
        );
    }
}
