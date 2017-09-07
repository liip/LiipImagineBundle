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

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\OptiPngPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\OptiPngPostProcessor
 */
class OptiPngPostProcessorTest extends AbstractPostProcessorTestCase
{
    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException
     * @expectedExceptionMessage the "level" option must be an int between 0 and 7
     */
    public function testInvalidLevelOption()
    {
        $this->getProcessArguments(['level' => 100]);
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException
     * @expectedExceptionMessage the "interlace_type" option must be either 0 or 1
     */
    public function testInvalidInterlaceOption()
    {
        $this->getProcessArguments(['interlace_type' => 10]);
    }

    /**
     * @group legacy
     *
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException
     * @expectedExceptionMessage the "strip" and "strip_all" options cannot both be set
     * @expectedDeprecation The "strip_all" option was deprecated in %s and will be removed in %s. Instead, use the "strip" option.
     */
    public function testInvalidStripOptionAndDeprecation()
    {
        $this->getProcessArguments(['strip_all' => true, 'strip' => 'all']);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The "strip_all" option was deprecated in %s and will be removed in %s. Instead, use the "strip" option.
     */
    public function testInvalidStripDeprecationMessage()
    {
        $arguments = $this->getProcessArguments(['strip_all' => true]);

        $this->assertSame('all', array_pop($arguments));
        $this->assertSame('-strip', array_pop($arguments));
    }

    /**
     * @return mixed[]
     */
    public static function provideSetupProcessBuilderData()
    {
        $data = [
            [[], ['-o7', '-strip', 'all']],
            [['level' => null], ['-o7', '-strip', 'all']],
            [['level' => 0], ['-o0', '-strip', 'all']],
            [['level' => 6], ['-o6', '-strip', 'all']],
            [['snip' => false], ['-o7', '-strip', 'all']],
            [['snip' => true], ['-o7', '-strip', 'all', '-snip']],
            [['preserve_attributes' => false], ['-o7', '-strip', 'all']],
            [['preserve_attributes' => true], ['-o7', '-strip', 'all', '-preserve']],
            [['interlace_type' => null], ['-o7', '-strip', 'all']],
            [['interlace_type' => 0], ['-o7', '-strip', 'all', '-i', 0]],
            [['interlace_type' => 1], ['-o7', '-strip', 'all', '-i', 1]],
            [['no_bit_depth_reductions' => false], ['-o7', '-strip', 'all']],
            [['no_bit_depth_reductions' => true], ['-o7', '-strip', 'all', '-nb']],
            [['no_color_type_reductions' => false], ['-o7', '-strip', 'all']],
            [['no_color_type_reductions' => true], ['-o7', '-strip', 'all', '-nc']],
            [['no_palette_reductions' => false], ['-o7', '-strip', 'all']],
            [['no_palette_reductions' => true], ['-o7', '-strip', 'all', '-np']],
            [['no_reductions' => false], ['-o7', '-strip', 'all']],
            [['no_reductions' => true], ['-o7', '-strip', 'all', '-nx']],
            [['level' => 4, 'snip' => true, 'preserve_attributes' => true, 'interlace_type' => 1, 'no_bit_depth_reductions' => true, 'no_palette_reductions' => true], ['-o4', '-strip', 'all', '-snip', '-preserve', '-i', 1, '-nb', '-np']],
        ];

        return array_map(function (array $d) {
            array_unshift($d[1], AbstractPostProcessorTestCase::getPostProcessAsFileExecutable());

            return $d;
        }, $data);
    }

    /**
     * @dataProvider provideSetupProcessBuilderData
     */
    public function testSetupProcessBuilder(array $options, array $expected)
    {
        $this->assertSame($expected, $this->getProcessArguments($options));
    }

    /**
     * @return mixed[]
     */
    public static function provideProcessData()
    {
        $file = file_get_contents(__FILE__);
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
     *
     * @param string $content
     * @param array  $options
     * @param string $expected
     */
    public function testProcess($content, array $options, $expected)
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
     *
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     *
     * @param array  $options
     * @param string $expected
     */
    public function testProcessError($content, array $options, $expected)
    {
        $process = $this->getPostProcessorInstance([static::getPostProcessAsFileFailingExecutable()]);
        $process->process(new Binary('content', 'image/png', 'png'), $options);
    }

    public function testProcessWithNonSupportedMimeType()
    {
        $binary = $this->getBinaryInterfaceMock();

        $binary
            ->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturn('application/x-php');

        $this->assertSame($binary, $this->getPostProcessorInstance()->process($binary, []));
    }

    /**
     * @param array $parameters
     *
     * @return OptiPngPostProcessor
     */
    protected function getPostProcessorInstance(array $parameters = [])
    {
        return new OptiPngPostProcessor($parameters[0] ?? static::getPostProcessAsFileExecutable());
    }
}
