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
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\CwebpPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\PngquantPostProcessor
 */
class CwebpPostProcessorTest extends AbstractPostProcessorTestCase
{
    public function testQOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('The "q" option must be an int between 0 and 100');

        $this->getProcessArguments(['q' => 101]);
    }

    public function testAlphaQOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('The "alphaQ" option must be an int between 0 and 100');

        $this->getProcessArguments(['alphaQ' => 101]);
    }

    public function testMOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('The "m" option must be an int between 0 and 6');

        $this->getProcessArguments(['m' => 7]);
    }

    public function testAlphaFilterOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('The "alphaFilter" option must be a string (none, fast or best)');

        $this->getProcessArguments(['alphaFilter' => 'dummy']);
    }

    public function testAlphaMethodOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('The "alphaMethod" option must be an int between 0 and 1');

        $this->getProcessArguments(['alphaMethod' => 7]);
    }

    public function testMetadataOptionThrowsOnOutOfScopeInt(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('The "metadata" option must be a list of string (all, none, exif, icc, xmp)');

        $this->getProcessArguments(['metadata' => 'dummy']);
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
            [['metadata' => 'all'], ['-metadata', 'all']],
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
            [['metadata' => 'all'], '-metadata all'],
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
            ->willReturn('application/x-php')
        ;

        $this->assertSame($binary, $this->getPostProcessorInstance()->process($binary, []));
    }

    protected function getPostProcessorInstance(array $parameters = []): CwebpPostProcessor
    {
        return new CwebpPostProcessor($parameters[0] ?? static::getPostProcessAsStdInExecutable());
    }
}
