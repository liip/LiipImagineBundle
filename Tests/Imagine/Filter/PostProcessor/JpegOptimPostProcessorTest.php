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
    /**
     * @group legacy
     * @expectedDeprecation The %s::setMax() method was deprecated in %s and will be removed in %s. You must setup the class state via its __construct() method. You can still pass filter-specific options to the process() method to overwrite behavior.
     */
    public function testDeprecatedSetMaxMethod(): void
    {
        $this->getPostProcessorInstance()->setMax(50);
    }

    /**
     * @group legacy
     * @expectedDeprecation The %s::setProgressive() method was deprecated in %s and will be removed in %s. You must setup the class state via its __construct() method. You can still pass filter-specific options to the process() method to overwrite behavior.
     */
    public function testDeprecatedSetProgressiveMethod(): void
    {
        $this->getPostProcessorInstance()->setProgressive(50);
    }

    /**
     * @group legacy
     * @expectedDeprecation The %s::setStripAll() method was deprecated in %s and will be removed in %s. You must setup the class state via its __construct() method. You can still pass filter-specific options to the process() method to overwrite behavior.
     */
    public function testDeprecatedSetStripAllMethod(): void
    {
        $this->getPostProcessorInstance()->setStripAll(50);
    }

    public function testInvalidLevelOption(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "quality" option must be an int between 0 and 100');

        $this->getProcessArguments(['quality' => 1000]);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The "max" option was deprecated in %s and will be removed in %s. Instead, use the "quality" option.
     */
    public function testOptionThrowsWhenBothMaxAndQualityAreSet(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('the "max" and "quality" options cannot both be set');

        $this->getProcessArguments(['max' => 50, 'quality' => 50]);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The "max" option was deprecated in %s and will be removed in %s. Instead, use the "quality" option.
     */
    public function testInvalidStripDeprecationMessage(): void
    {
        $this->assertContains('--max=50', $this->getProcessArguments(['max' => 50]));
    }

    public static function provideProcessArgumentsData(): array
    {
        $data = [
            [[], ['--strip-all', '--all-progressive']],
            [['strip_all' => false], ['--all-progressive']],
            [['strip_all' => true], ['--strip-all', '--all-progressive']],
            [['quality' => 50], ['--strip-all', '--max=50', '--all-progressive']],
            [['progressive' => false], ['--strip-all', '--all-normal']],
            [['progressive' => true], ['--strip-all', '--all-progressive']],
        ];

        return array_map(function (array $d) {
            array_unshift($d[1], AbstractPostProcessorTestCase::getPostProcessAsFileExecutable());

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

        $process = $this->getPostProcessorInstance([static::getPostProcessAsFileFailingExecutable()]);
        $process->process(new Binary('content', 'image/jpeg', 'jpeg'), $options);
    }

    protected function getPostProcessorInstance(array $parameters = []): JpegOptimPostProcessor
    {
        return new JpegOptimPostProcessor($parameters[0] ?? static::getPostProcessAsFileExecutable());
    }
}
