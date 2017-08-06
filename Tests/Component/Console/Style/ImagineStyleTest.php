<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Component\Console\IO;

use Liip\ImagineBundle\Component\Console\Style\ImagineStyle;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Fixtures\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

/**
 * @covers \Liip\ImagineBundle\Component\Console\Style\ImagineStyle
 */
class ImagineStyleTest extends AbstractTest
{
    /**
     * @param string $format
     * @param array  $replacements
     *
     * @dataProvider provideTextData
     */
    public function testText(string $format, array $replacements)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->text($format, $replacements);

        $this->assertContains(vsprintf($format, $replacements), $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideTextData(): \Generator
    {
        yield ['Text with <em>0</em> replacements.', []];
        yield ['Text with a <comment>%s string</comment>.', ['replacement']];
        yield ['Text with <options=bold>%d %s</>, a <info>digit</info> and <info>string</info>.', [2, 'replacements']];
        yield ['%s %s (%d) <fg=red>%s</> %s %s!', ['Text', 'with', 6, 'ONLY', 'replacement', 'values']];
    }

    /**
     * @param string $format
     * @param array  $replacements
     *
     * @dataProvider provideTextWithoutDecorationData
     */
    public function testTextWithoutDecoration(string $format, array $replacements)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput(), false);
        $style->text($format, $replacements);

        $this->assertContains(strip_tags(vsprintf($format, $replacements)), $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideTextWithoutDecorationData(): \Generator
    {
        return static::provideTextData();
    }

    /**
     * @param string $format
     * @param array  $replacements
     *
     * @dataProvider provideLineData
     */
    public function testLine(string $format, array $replacements)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->line($format, $replacements);

        $this->assertContains(vsprintf($format, $replacements).PHP_EOL, $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideLineData(): \Generator
    {
        return static::provideTextData();
    }

    /**
     * @param int    $newlineCount
     * @param string $separator
     *
     * @dataProvider provideNewlineData
     */
    public function testNewline(int $newlineCount, string $separator)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->text($separator);
        $style->newline($newlineCount);
        $style->text($separator);

        $this->assertContains(sprintf('%1$s%2$s%1$s', $separator, str_repeat(PHP_EOL, $newlineCount)), $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideNewlineData(): \Generator
    {
        for ($i = 0; $i <= 200; $i += 50) {
            yield [$i, sprintf('[abcdef0123-%d]', $i)];
        }
    }

    /**
     * @param string      $character
     * @param int|null    $width
     * @param string|null $color
     *
     * @dataProvider provideSeparatorData
     */
    public function testSeparator(string $character, int $width = null, string $color = null)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->separator($character, $width, $color);

        if (null === $width) {
            $width = class_exists(Terminal::class) ? (new Terminal())->getWidth() : 80;
        }

        $this->assertContains(sprintf('<fg=%s;>%s</>', $color ?: 'default', str_repeat($character, $width)), $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideSeparatorData(): \Generator
    {
        $widths = [null, 20, 40, 80, 160, 320, 640];
        $separators = ['-', '~', '==', '___', '--{{ <a-complex-separator> }}--'];

        foreach (static::getConsoleColors() as $color) {
            foreach ($widths as $width) {
                foreach ($separators as $chars) {
                    yield [$chars, $width, $color];
                }
            }
        }
    }

    /**
     * @param string      $title
     * @param string|null $type
     * @param string|null $fg
     * @param string|null $bg
     * @param bool        $decoration
     *
     * @dataProvider provideTitleData
     */
    public function testTitle(string $title, string $type = null, string $fg = null, string $bg = null, bool $decoration)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput(), $decoration);
        $style->title($title, $type, $fg, $bg);

        if ($decoration) {
            if ($type) {
                $expected = sprintf('<fg=%s;bg=%s> [%s] %s', $fg ?: 'white', $bg ?: 'magenta', $type, $title);
            } else {
                $expected = sprintf('<fg=%s;bg=%s> %s', $fg ?: 'white', $bg ?: 'magenta', $title);
            }
        } else {
            if ($type) {
                $expected = sprintf('# [%s] %s', $type, $title);
            } else {
                $expected = sprintf('# %s', $title);
            }
        }

        $this->assertContains($expected, $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideTitleData(): \Generator
    {
        $titles = [
            'A simple title',
            'A Type' => 'Title with type context',
        ];

        foreach (static::getConsoleColors() as $color) {
            foreach ($titles as $type => $title) {
                yield [$title, is_string($type) ? $type : null, $color, $color, true];
                yield [$title, is_string($type) ? $type : null, $color, $color, false];
            }
        }
    }

    /**
     * @param string $type
     * @param string $expectedFormat
     * @param string $format
     * @param array  $replacements
     * @param bool   $decoration
     *
     * @dataProvider provideBlockTypesData
     */
    public function testBlockTypes(string $type, string $expectedFormat, string $format, array $replacements = [], bool $decoration)
    {
        $blockMethod = sprintf('%sBlock', $type);
        $style = $this->createImagineStyle($output = $this->createBufferedOutput(), $decoration);

        if (!is_callable([$style, $blockMethod])) {
            static::fail(sprintf('Required method "%s" for "%s" block type is not callable!', $blockMethod, $type));
        }

        $style->{$blockMethod}($format, $replacements);
        $compiled = vsprintf(strip_tags($format), $replacements);

        $this->assertContains(sprintf($expectedFormat, $compiled), $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideBlockTypesData(): \Generator
    {
        $types = [
            'okay' => ['<fg=black;bg=green> - [OKAY] %s', '[OKAY] %s'],
            'note' => ['<fg=yellow;bg=black> / [NOTE] %s', '[NOTE] %s'],
            'crit' => ['<fg=white;bg=red> # [ERROR] %s', '[ERROR] %s'],
        ];

        $instructions = [
            'This is a block, right? (But which one is it?)' => [],
            'A %s message!' => ['block'],
            'This has %d string %s (with a repeating %2$s).' => [1, 'replacement'],
        ];

        foreach ($types as $type => $expectations) {
            foreach ($instructions as $format => $replacements) {
                yield [$type, $expectations[0], $format, $replacements, true];
                yield [$type, $expectations[1], $format, $replacements, false];
            }
        }
    }

    /**
     * @param string $type
     * @param string $expectedFormat
     * @param string $string
     * @param array  $environment
     * @param bool   $decoration
     *
     * @dataProvider provideBlockSizesData
     */
    public function testBlockSizes(string $type, string $expectedFormat, string $string, array $environment, bool $decoration)
    {
        $blockMethod = sprintf('%sBlock', $type);
        $style = $this->createImagineStyle($output = $this->createBufferedOutput(), $decoration);

        if (!is_callable([$style, $blockMethod])) {
            static::fail(sprintf('Required method "%s" for "%s" block type is not callable!', $blockMethod, $type));
        }

        $style->{$blockMethod}($string, ...$environment);
        $expected = sprintf($expectedFormat, $environment[1], $environment[2], $environment[3], $environment[0], $string);

        $this->assertContains($expected, $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideBlockSizesData(): \Generator
    {
        $sizes = [
            'small' => '<fg=%s;bg=%s> %s [%s] %s',
            'large' => '<fg=%s;bg=%s> %s [%s] %s',
        ];

        $strings = [
            'This is a block, right? (But which one is it?)',
            'A short block!',
        ];

        $environments = [
            ['TYPE', 'white', 'blue', 'PREFIX', true],
            ['ERROR', 'black', 'red', 'ERR', true],
        ];

        foreach ($sizes as $type => $format) {
            foreach ($strings as $string) {
                foreach ($environments as $environment) {
                    yield [$type, $format, $string, $environment, true];
                }
            }
        }
    }

    /**
     * @param int $count
     *
     * @dataProvider provideSpaceData
     */
    public function testSpace(int $count)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->space($count);

        $this->assertContains(str_repeat(' ', $count), $output->getBuffer());
    }

    /**
     * @return \Generator
     */
    public static function provideSpaceData(): \Generator
    {
        for ($spaces = 1; $spaces < 200; $spaces += 50) {
            yield [$spaces];
        }
    }

    /**
     * @param string      $status
     * @param string|null $fg
     * @param string|null $bg
     *
     * @dataProvider provideStatusData
     */
    public function testStatus(string $status, string $fg = null, string $bg = null)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->status($status, $fg, $bg);

        $this->assertContains($fg ?: 'default', $output->getBuffer());
        $this->assertContains($bg ?: 'default', $output->getBuffer());
        $this->assertContains(sprintf('(%s)', $status), strip_tags($output->getBuffer()));
    }

    /**
     * @return \Generator
     */
    public static function provideStatusData(): \Generator
    {
        $states = ['resolved', 'skipped', 'failed', 'cached', 'ignored'];

        foreach (static::getConsoleColors() as $color) {
            foreach ($states as $state) {
                yield [$state, $color, $color];
            }
        }
    }

    /**
     * @param string      $item
     * @param string      $group
     * @param string|null $fg
     * @param string|null $bg
     *
     * @dataProvider provideGroupData
     */
    public function testGroup(string $item, string $group, string $fg = null, string $bg = null)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->group($item, $group, $fg, $bg);

        $this->assertContains($fg ?: 'default', $output->getBuffer());
        $this->assertContains($bg ?: 'default', $output->getBuffer());
        $this->assertContains(sprintf('%s[%s]', $item, $group), strip_tags($output->getBuffer()));
    }

    /**
     * @return \Generator
     */
    public static function provideGroupData(): \Generator
    {
        foreach (static::getConsoleColors() as $color) {
            for ($i = 1; $i < 10; $i += 3) {
                for ($j = 1000; $j < 1004; $j++) {
                    yield [sprintf('item-%s', $i), sprintf('group-%s', $j), $color, $color];
                }
            }
        }
    }

    /**
     * @param string $format
     * @param array  $replacements
     *
     * @dataProvider provideInvalidFormatAndReplacementsData
     *
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessageRegExp {Invalid string format "[^"]+" or replacements "[^"]+".}
     */
    public function testInvalidFormatAndReplacements(string $format, array $replacements)
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->text($format, $replacements);
    }

    /**
     * @return \Generator
     */
    public static function provideInvalidFormatAndReplacementsData(): \Generator
    {
        yield ['%s %s', ['bad-replacements-array']];
        yield ['%s %s %s %s %s', ['not', 'enough', 'replacements']];
        yield ['%s %d %s', ['missing', 1]];
    }

    /**
     * @param OutputInterface $output
     * @param bool            $decoration
     *
     * @return ImagineStyle
     */
    private function createImagineStyle(OutputInterface $output, bool $decoration = true): ImagineStyle
    {
        return new ImagineStyle(new ArrayInput([]), $output, $decoration);
    }

    /**
     * @return BufferedOutput
     */
    private function createBufferedOutput(): BufferedOutput
    {
        return new BufferedOutput();
    }

    /**
     * @return array
     */
    private static function getConsoleColors(): array
    {
        return [null, 'default', 'red', 'blue', 'yellow', 'white', 'magenta', 'cyan'];
    }
}
