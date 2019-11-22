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

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\MozJpegPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\MozJpegPostProcessor
 */
class MozJpegPostProcessorTest extends AbstractPostProcessorTestCase
{
    /**
     * @group legacy
     *
     * @expectedDeprecation The %s::setQuality() method was deprecated in %s and will be removed in %s. You must setup the class state via its __construct() method. You can still pass filter-specific options to the process() method to overwrite behavior.
     */
    public function testDeprecatedSetQualityMethod(): void
    {
        $this->getPostProcessorInstance()->setQuality(50);
    }

    public static function provideProcessArgumentsData(): array
    {
        $data = [
            [[], ['-quant-table', 2, '-optimise']],
            [['quant_table' => 10], ['-quant-table', 10, '-optimise']],
            [['optimise' => false], ['-quant-table', 2]],
            [['optimise' => true], ['-quant-table', 2, '-optimise']],
            [['quality' => 50], ['-quant-table', 2, '-optimise', '-quality', 50]],
            [['quant_table' => 4, 'optimise' => true, 'quality' => 100], ['-quant-table', 4, '-optimise', '-quality', 100]],
        ];

        return array_map(function (array $d) {
            array_unshift($d[1], AbstractPostProcessorTestCase::getPostProcessAsStdInExecutable());

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
            [[], '-quant-table 2 -optimise'],
            [['quant_table' => 10], '-quant-table 10 -optimise'],
            [['optimise' => false], '-quant-table 2'],
            [['optimise' => true], '-quant-table 2 -optimise'],
            [['quality' => 50], '-quant-table 2 -optimise -quality 50'],
            [['quant_table' => 4, 'optimise' => true, 'quality' => 100], '-quant-table 4 -optimise -quality 100'],
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

        $process = $this->getPostProcessorInstance([static::getPostProcessAsStdInErrorExecutable()]);
        $process->process(new Binary('content', 'image/jpeg', 'jpeg'), $options);
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

    protected function getPostProcessorInstance(array $parameters = []): MozJpegPostProcessor
    {
        return new MozJpegPostProcessor($parameters[0] ?? static::getPostProcessAsStdinExecutable());
    }
}
