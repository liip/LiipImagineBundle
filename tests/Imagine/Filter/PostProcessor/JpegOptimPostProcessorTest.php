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

use Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor
 */
class JpegOptimPostProcessorTest extends AbstractPostProcessorTestCase
{
    public function testInvalidLevelOption(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "quality" option must be an int between 0 and 100');

        $this->getProcessArguments(['quality' => 1000]);
    }

    public static function provideProcessArgumentsData(): array
    {
        $data = [
            [[], [], ['--strip-all', '--all-progressive']],
            [[], ['strip_all' => false], ['--all-progressive']],
            [[], ['strip_all' => true], ['--strip-all', '--all-progressive']],
            [[], ['quality' => 50], ['--strip-all', '--max=50', '--all-progressive']],
            [[], ['progressive' => false], ['--strip-all', '--all-normal']],
            [[], ['progressive' => true], ['--strip-all', '--all-progressive']],
            [[null, true, 85], ['progressive' => true], ['--strip-all', '--max=85', '--all-progressive']],
        ];

        return array_map(function (array $d) {
            array_unshift($d[2], AbstractPostProcessorTestCase::getPostProcessAsFileExecutable());

            return $d;
        }, $data);
    }

    /**
     * @dataProvider provideProcessArgumentsData
     */
    public function testProcessArguments(array $parameters, array $options, array $expected): void
    {
        $result = $this
            ->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance($parameters), 'getProcessArguments')
            ->invokeArgs($processor, [$options]);

        $this->assertSame($expected, $result);
    }

    public function testProcessWithNonSupportedMimeType(): void
    {
        $binary = $this->getBinaryInterfaceMock();

        $binary
            ->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturn('application/x-php');

        $this->assertSame($binary, $this->getPostProcessorInstance()->process($binary));
    }

    public static function provideProcessData(): array
    {
        $file = file_get_contents(__FILE__);
        $data = [
            [[], '--strip-all --all-progressive'],
            [['strip_all' => false], '--all-progressive'],
            [['strip_all' => true], '--strip-all --all-progressive'],
            [['quality' => 50], '--strip-all --max=50 --all-progressive'],
            [['progressive' => false], '--strip-all --all-normal'],
            [['progressive' => true], '--strip-all --all-progressive'],
        ];

        return array_map(function ($d) use ($file) {
            array_unshift($d, $file);

            return $d;
        }, $data);
    }

    /**
     * @dataProvider provideProcessData
     */
    public function testProcess(string $content, array $options, string $expected): void
    {
        $file = sys_get_temp_dir().'/test.jpeg';
        file_put_contents($file, $content);

        $process = $this->getPostProcessorInstance();
        $result = $process->process(new FileBinary($file, 'image/jpeg', 'jpeg'), $options);

        $this->assertStringContainsString($expected, $result->getContent());
        $this->assertStringContainsString($content, $result->getContent());

        @unlink($file);
    }

    /**
     * @dataProvider provideProcessData
     */
    public function testProcessError(string $content, array $options, string $expected): void
    {
        $this->expectException(ProcessFailedException::class);

        $process = $this->getPostProcessorInstance([static::getPostProcessAsFileFailingExecutable()]);
        $process->process(new Binary('content', 'image/jpeg', 'jpeg'), $options);
    }

    protected function getPostProcessorInstance(array $parameters = []): JpegOptimPostProcessor
    {
        $parameters[0] = $parameters[0] ?? static::getPostProcessAsFileExecutable();

        return new JpegOptimPostProcessor(...$parameters);
    }
}
