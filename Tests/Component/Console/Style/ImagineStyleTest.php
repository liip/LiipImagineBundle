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

use Generator;
use Liip\ImagineBundle\Component\Console\Style\ImagineStyle;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\Fixtures\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Liip\ImagineBundle\Component\Console\Style\ImagineStyle
 */
class ImagineStyleTest extends AbstractTest
{
    /**
     * @dataProvider provideTextData
     */
    public function testText(string $format, array $replacements): void
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->text($format, $replacements);

        $this->assertStringContainsString(vsprintf($format, $replacements), $output->getBuffer());
    }

    public static function provideTextData(): Generator
    {
        yield ['Text with <em>0</em> replacements.', []];
        yield ['Text with a <comment>%s string</comment>.', ['replacement']];
        yield ['Text with <options=bold>%d %s</>, a <info>digit</info> and <info>string</info>.', [2, 'replacements']];
        yield ['%s %s (%d) <fg=red>%s</> %s %s!', ['Text', 'with', 6, 'ONLY', 'replacement', 'values']];
    }

    /**
     * @dataProvider provideTextWithoutDecorationData
     */
    public function testTextWithoutDecoration(string $format, array $replacements): void
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput(), false);
        $style->text($format, $replacements);

        $this->assertStringContainsString(strip_tags(vsprintf($format, $replacements)), $output->getBuffer());
    }

    public static function provideTextWithoutDecorationData(): Generator
    {
        return static::provideTextData();
    }

    /**
     * @dataProvider provideLineData
     */
    public function testLine(string $format, array $replacements): void
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->line($format, $replacements);

        $this->assertStringContainsString(vsprintf($format, $replacements).PHP_EOL, $output->getBuffer());
    }

    public static function provideLineData(): Generator
    {
        return static::provideTextData();
    }

    /**
     * @dataProvider provideNewlineData
     */
    public function testNewline(int $newlineCount, string $separator): void
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->text($separator);
        $style->newline($newlineCount);
        $style->text($separator);

        $this->assertStringContainsString(sprintf('%1$s%2$s%1$s', $separator, str_repeat(PHP_EOL, $newlineCount)), $output->getBuffer());
    }

    public static function provideNewlineData(): Generator
    {
        for ($i = 0; $i <= 200; $i += 50) {
            yield [$i, sprintf('[abcdef0123-%d]', $i)];
        }
    }

    /**
     * @dataProvider provideTitleData
     */
    public function testTitle(string $title, string $type = null, bool $decoration): void
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput(), $decoration);
        $style->title($title, $type);

        if ($decoration) {
            if ($type) {
                $expected = sprintf('<fg=%s;bg=%s> [%s] %s', 'white', 'cyan', $type, $title);
            } else {
                $expected = sprintf('<fg=%s;bg=%s> %s', 'white', 'cyan', $title);
            }
        } else {
            if ($type) {
                $expected = sprintf('# [%s] %s', $type, $title);
            } else {
                $expected = sprintf('# %s', $title);
            }
        }

        $this->assertStringContainsString($expected, $output->getBuffer());
    }

    public static function provideTitleData(): Generator
    {
        $titles = [
            'A simple title',
            'A Type' => 'Title with type context',
        ];

        foreach ($titles as $type => $title) {
            yield [$title, is_string($type) ? $type : null, true];
            yield [$title, is_string($type) ? $type : null, false];
        }
    }

    /**
     * @dataProvider provideBlockTypesData
     */
    public function testBlockTypes(string $type, string $expectedFormat, string $format, array $replacements, bool $decoration): void
    {
        $blockMethod = sprintf('%sBlock', $type);
        $style = $this->createImagineStyle($output = $this->createBufferedOutput(), $decoration);

        if (!is_callable([$style, $blockMethod])) {
            static::fail(sprintf('Required method "%s" for "%s" block type is not callable!', $blockMethod, $type));
        }

        $style->{$blockMethod}($format, $replacements);
        $compiled = vsprintf(strip_tags($format), $replacements);

        $this->assertStringContainsString(sprintf($expectedFormat, $compiled), $output->getBuffer());
    }

    public static function provideBlockTypesData(): Generator
    {
        $types = [
            'okay' => ['<fg=black;bg=green> - [OKAY] %s', '[OKAY] %s'],
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
     * @dataProvider provideStatusData
     */
    public function testStatus(string $status, string $fg = null, string $bg = null): void
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->status($status, $fg);

        $this->assertStringContainsString($fg ?: 'default', $output->getBuffer());
        $this->assertStringContainsString($bg ?: 'default', $output->getBuffer());
        $this->assertStringContainsString(sprintf('(%s)', $status), strip_tags($output->getBuffer()));
    }

    public static function provideStatusData(): Generator
    {
        $states = ['resolved', 'skipped', 'failed', 'cached', 'ignored'];

        foreach (static::getConsoleColors() as $color) {
            foreach ($states as $state) {
                yield [$state, $color, $color];
            }
        }
    }

    /**
     * @dataProvider provideGroupData
     */
    public function testGroup(string $item, string $group, string $fg = null, string $bg = null): void
    {
        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->group($item, $group, $fg, $bg);

        $this->assertStringContainsString($fg ?: 'default', $output->getBuffer());
        $this->assertStringContainsString($bg ?: 'default', $output->getBuffer());
        $this->assertStringContainsString(sprintf('%s[%s]', $item, $group), strip_tags($output->getBuffer()));
    }

    public static function provideGroupData(): Generator
    {
        foreach (static::getConsoleColors() as $color) {
            for ($i = 1; $i < 10; $i += 3) {
                for ($j = 1000; $j < 1004; ++$j) {
                    yield [sprintf('item-%s', $i), sprintf('group-%s', $j), $color, $color];
                }
            }
        }
    }

    /**
     * @dataProvider provideInvalidFormatAndReplacementsData
     */
    public function testInvalidFormatAndReplacements(string $format, array $replacements): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('{Invalid string format "[^"]+" or replacements "[^"]+".}');

        $style = $this->createImagineStyle($output = $this->createBufferedOutput());
        $style->text($format, $replacements);
    }

    public static function provideInvalidFormatAndReplacementsData(): Generator
    {
        yield ['%s %s', ['bad-replacements-array']];
        yield ['%s %s %s %s %s', ['not', 'enough', 'replacements']];
        yield ['%s %d %s', ['missing', 1]];
    }

    private function createImagineStyle(OutputInterface $output, bool $decoration = true): ImagineStyle
    {
        return new ImagineStyle(new ArrayInput([]), $output, $decoration);
    }

    private function createBufferedOutput(): BufferedOutput
    {
        return new BufferedOutput();
    }

    private static function getConsoleColors(): array
    {
        return [null, 'default', 'red', 'blue', 'yellow', 'white', 'magenta', 'cyan'];
    }
}
