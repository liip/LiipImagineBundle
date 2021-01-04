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
use ValueError;

/**
 * @internal
 */
final class ImagineStyle
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var bool
     */
    private $decoration;

    public function __construct(InputInterface $input, OutputInterface $output, bool $decoration = true)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->decoration = $decoration;
    }

    public function text(string $string, array $replacements = []): self
    {
        $this->io->write($this->compileString($string, $replacements));

        return $this;
    }

    public function line(string $string, array $replacements = []): self
    {
        $this->io->writeln($this->compileString($string, $replacements));

        return $this;
    }

    public function newline(int $count = 1): self
    {
        $this->io->newLine($count);

        return $this;
    }

    public function status(string $status, string $fg = null): self
    {
        return $this->text(
            sprintf('<fg=%2$s>(</><fg=%2$s;options=bold>%1$s</><fg=%2$s>)</>', $status, $fg ?: 'default')
        );
    }

    public function group(string $item, string $group, string $fg = null): self
    {
        $this->text(
            sprintf('<fg=%3$s;options=bold>%1$s[</><fg=%3$s>%2$s</><fg=%3$s;options=bold>]</>', $item, $group, $fg ?: 'default')
        );

        return $this->space();
    }

    public function title(string $title, string $type = null): self
    {
        if (!$this->decoration) {
            return $this->plainTitle($title, $type);
        }

        return $this->block($title, $type, 'white', 'cyan');
    }

    public function okayBlock(string $string, array $replacements = []): self
    {
        return $this->largeBlock($this->compileString(strip_tags($string), $replacements), 'OKAY', 'black', 'green', '-');
    }

    public function critBlock(string $string, array $replacements = []): self
    {
        return $this->largeBlock($this->compileString(strip_tags($string), $replacements), 'ERROR', 'white', 'red', '#');
    }

    private function largeBlock(string $string, string $type, string $fg = null, string $bg = null, string $prefix = null): self
    {
        return $this->block($string, $type, $fg, $bg, $prefix, true);
    }

    private function space(int $count = 1): self
    {
        return $this->text(str_repeat(' ', $count));
    }

    private function plainTitle(string $title, string $type = null): self
    {
        $this->newline();

        if ($type) {
            $this->line('# [%s] %s', [$type, $title]);
        } else {
            $this->line('# %s', [$title]);
        }

        return $this->newline();
    }

    private function block(string $string, string $type = null, string $fg = null, string $bg = null, string $prefix = null, bool $padding = true): self
    {
        if (!$this->decoration) {
            return $this->plainBlock($string, $type);
        }

        $this->io->block($string, $type, sprintf('fg=%s;bg=%s', $fg ?: 'default', $bg ?: 'default'), $prefix ? sprintf(' %s ', $prefix) : ' ', $padding);

        return $this;
    }

    private function plainBlock(string $string, string $type): self
    {
        return $this
            ->newline()
            ->line('[%s] %s', [$type, $string])
            ->newline();
    }

    private function compileString(string $format, array $replacements = []): string
    {
        if (!$this->decoration) {
            $format = strip_tags($format);
        }

        if (0 === \count($replacements)) {
            return $format;
        }

        try {
            if (false !== $compiled = @vsprintf($format, $replacements)) {
                return $compiled;
            }
        } catch (ValueError $error) {
        }

        throw new InvalidArgumentException(sprintf('Invalid string format "%s" or replacements "%s".', $format, implode(', ', array_map(function ($replacement) { return var_export($replacement, true); }, $replacements))));
    }
}
