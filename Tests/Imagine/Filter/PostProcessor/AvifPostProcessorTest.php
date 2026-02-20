<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\AvifPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AvifPostProcessor
 */
class AvifPostProcessorTest extends AbstractPostProcessorTestCase
{
    public function testQualityOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "quality" with value 101 is invalid.');

        $this->getProcessArguments(['quality' => 101]);
    }

    public function testSpeedOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "speed" with value 11 is invalid.');

        $this->getProcessArguments(['speed' => 11]);
    }

    public function testJobsOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "jobs" with value -1 is invalid.');

        $this->getProcessArguments(['jobs' => -1]);
    }

    public static function provideProcessArgumentsData(): array
    {
        $data = [
            [[], []],
            [['quality' => 100], ['--min', 0, '--max', 0]],
            [['quality' => 0], ['--min', 63, '--max', 63]],
            [['quality' => 75], ['--min', 16, '--max', 16]],
            [['speed' => 6], ['--speed', 6]],
            [['jobs' => 4], ['--jobs', 4]],
        ];

        return array_map(static function (array $d) {
            array_unshift($d[1], AbstractPostProcessorTestCase::getPostProcessOutputFileExecutable());

            return $d;
        }, $data);
    }

    /**
     * @dataProvider provideProcessArgumentsData
     */
    public function testProcessArguments(array $options, array $expected): void
    {
        $this->assertSame($expected, $this->getProcessArguments($options));
    }

    public static function provideProcessData(): array
    {
        $file = 'stdio-file-content-string';
        $data = [
            [[], ''],
            [['quality' => 100], '--min 0 --max 0'],
            [['speed' => 6], '--speed 6'],
            [['jobs' => 4], '--jobs 4'],
        ];

        return array_map(static function ($d) use ($file) {
            array_unshift($d, $file);

            return $d;
        }, $data);
    }

    /**
     * @dataProvider provideProcessData
     */
    public function testProcess(string $content, array $options, string $expected): void
    {
        $file = sys_get_temp_dir().'/test.avif';
        file_put_contents($file, $content);

        $process = $this->getPostProcessorInstance();
        $result = $process->process(new FileBinary($file, 'image/avif', 'avif'), $options);

        $this->assertStringContainsString($expected, $result->getContent());
        $this->assertStringContainsString('argument-list:', $result->getContent());

        @unlink($file);
    }

    /**
     * @dataProvider provideProcessData
     */
    public function testProcessError(string $content, array $options, string $expected): void
    {
        $this->expectException(ProcessFailedException::class);

        $process = $this->getPostProcessorInstance([static::getPostProcessAsFileFailingExecutable()]);
        $process->process(new Binary('content', 'image/avif', 'avif'), $options);
    }

    public function testProcessWithNonSupportedMimeType(): void
    {
        $binary = $this->getBinaryInterfaceMock();

        $binary
            ->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturn('application/x-php');

        $this->assertSame($binary, $this->getPostProcessorInstance()->process($binary, []));
    }

    /**
     * AvifPostProcessor acts as an optimizer only; it should ignore non-AVIF inputs.
     */
    public function testProcessIgnoresNonAvif(): void
    {
        $content = 'jpeg-content';
        $file = sys_get_temp_dir().'/test.jpg';
        file_put_contents($file, $content);

        $process = $this->getPostProcessorInstance();
        $original = new FileBinary($file, 'image/jpeg', 'jpg');
        $result = $process->process($original, []);

        $this->assertSame($original, $result);

        @unlink($file);
    }

    protected function getPostProcessorInstance(array $parameters = []): AvifPostProcessor
    {
        return new AvifPostProcessor($parameters[0] ?? static::getPostProcessOutputFileExecutable());
    }
}
