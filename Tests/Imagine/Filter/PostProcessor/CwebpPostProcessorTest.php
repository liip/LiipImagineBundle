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

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\CwebpPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\PngquantPostProcessor
 */
class CwebpPostProcessorTest extends AbstractPostProcessorTestCase
{
    public function testQOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "q" with value 101 is invalid.');

        $this->getProcessArguments(['q' => 101]);
    }

    public function testAlphaQOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "alphaQ" with value 101 is invalid.');

        $this->getProcessArguments(['alphaQ' => 101]);
    }

    public function testMOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "m" with value 7 is invalid.');

        $this->getProcessArguments(['m' => 7]);
    }

    public function testAlphaFilterOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "alphaFilter" with value "dummy" is invalid.');

        $this->getProcessArguments(['alphaFilter' => 'dummy']);
    }

    public function testAlphaMethodOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "alphaMethod" with value 7 is invalid.');

        $this->getProcessArguments(['alphaMethod' => 7]);
    }

    public function testMetadataOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "metadata" with value array is invalid.');

        $this->getProcessArguments(['metadata' => ['dummy']]);
    }

    public static function provideProcessArgumentsData(): array
    {
        $data = [
            [[], []],
            [['q' => 80], ['-q', 80]],
            [['alphaQ' => 80], ['-alpha_q', 80]],
            [['m' => 6], ['-m', 6]],
            [['alphaFilter' => 'best'], ['-alpha_filter', 'best']],
            [['alphaMethod' => 0], ['-alpha_method', 0]],
            [['exact' => true], ['-exact']],
            [['metadata' => ['exif', 'icc']], ['-metadata', 'exif,icc']],
        ];

        return array_map(static function (array $d) {
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
            [[], ''],
            [['q' => 80], '-q 80'],
            [['alphaQ' => 80], '-alpha_q 80'],
            [['m' => 6], '-m 6'],
            [['alphaFilter' => 'best'], '-alpha_filter best'],
            [['alphaMethod' => 0], '-alpha_method 0'],
            [['exact' => true], '-exact'],
            [['metadata' => ['all']], '-metadata all'],
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
        $file = sys_get_temp_dir().'/test.webp';
        file_put_contents($file, $content);

        $process = $this->getPostProcessorInstance();
        $result = $process->process(new FileBinary($file, 'image/webp', 'webp'), $options);

        $this->assertStringContainsString($expected, $result->getContent());

        @unlink($file);
    }

    /**
     * @dataProvider provideProcessData
     */
    public function testProcessError(string $content, array $options, string $expected): void
    {
        $this->expectException(ProcessFailedException::class);

        $process = $this->getPostProcessorInstance([static::getPostProcessAsStdInErrorExecutable()]);
        $process->process(new Binary('content', 'image/webp', 'webp'), $options);
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

    protected function getPostProcessorInstance(array $parameters = []): CwebpPostProcessor
    {
        return new CwebpPostProcessor($parameters[0] ?? static::getPostProcessAsStdInExecutable());
    }
}
