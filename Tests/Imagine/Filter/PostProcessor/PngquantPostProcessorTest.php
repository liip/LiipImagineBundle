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
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PngquantPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\PngquantPostProcessor
 */
class PngquantPostProcessorTest extends AbstractPostProcessorTestCase
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

    /**
     * @group legacy
     *
     * @expectedDeprecation Passing the "quality" option as a string was deprecated in %s and will be removed in %s. Instead, pass wither an integer representing the max value or an array representing the minimum and maximum values.
     */
    public function testQualityOptionDeprecation(): void
    {
        $this->getProcessArguments(['quality' => '0-100']);
    }

    public function testQualityOptionThrowsOnLargerMinThanMaxValue(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "quality" option cannot have a greater minimum value value than maximum quality value');

        $this->getProcessArguments(['quality' => [75, 25]]);
    }

    public function testQualityOptionThrowsOnOutOfScopeMaxInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "quality" option value(s) must be an int between 0 and 100');

        $this->getProcessArguments(['quality' => [25, 1000]]);
    }

    public function testQualityOptionThrowsOnOutOfScopeMinInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "quality" option value(s) must be an int between 0 and 100');

        $this->getProcessArguments(['quality' => [-1000, 25]]);
    }

    public function testSpeedOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "speed" option must be an int between 1 and 11');

        $this->getProcessArguments(['speed' => 15]);
    }

    public function testDitheringOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "dithering" option must be a float between 0 and 1 or a bool');

        $this->getProcessArguments(['dithering' => 2]);
    }

    public static function provideProcessArgumentsData(): array
    {
        $data = [
            [[], ['80-100']],
            [['quality' => null], ['80-100']],
            [['quality' => [80, 100]], ['80-100']],
            [['quality' => [100]], ['0-100']],
            [['quality' => '80'], ['0-80']],
            [['speed' => null], ['80-100']],
            [['speed' => 4], ['80-100', '--speed', 4]],
            [['dithering' => null], ['80-100']],
            [['dithering' => false], ['80-100', '--nofs']],
            [['dithering' => 0.5], ['80-100', '--floyd', 0.5]],
        ];

        return array_map(function (array $d) {
            array_unshift($d[1], '--quality');
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
            [[], '--quality 80-100'],
            [['quality' => null], '--quality 80-100'],
            [['quality' => [80, 100]], '--quality 80-100'],
            [['quality' => [100]], '--quality 0-100'],
            [['quality' => '80'], '--quality 0-80'],
            [['speed' => null], '--quality 80-100'],
            [['speed' => 4], '--quality 80-100 --speed 4'],
            [['dithering' => null], '--quality 80-100'],
            [['dithering' => false], '--quality 80-100 --nofs'],
            [['dithering' => 0.5], '--quality 80-100 --floyd 0.5'],
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
        $file = sys_get_temp_dir().'/test.png';
        file_put_contents($file, $content);

        $process = $this->getPostProcessorInstance();
        $result = $process->process(new FileBinary($file, 'image/png', 'png'), $options);

        $this->assertContains($expected, $result->getContent());
        $this->assertContains($content, $result->getContent());

        @unlink($file);
    }

    /**
     * @dataProvider provideProcessData
     */
    public function testProcessError(string $content, array $options, string $expected): void
    {
        $this->expectException(ProcessFailedException::class);

        $process = $this->getPostProcessorInstance([static::getPostProcessAsStdInErrorExecutable()]);
        $process->process(new Binary('content', 'image/png', 'png'), $options);
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

    protected function getPostProcessorInstance(array $parameters = []): PngquantPostProcessor
    {
        return new PngquantPostProcessor($parameters[0] ?? static::getPostProcessAsStdInExecutable());
    }
}
