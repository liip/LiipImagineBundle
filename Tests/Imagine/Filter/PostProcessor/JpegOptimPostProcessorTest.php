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

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor
 */
class JpegOptimPostProcessorTest extends AbstractPostProcessorTestCase
{
   /**
     * @group legacy
     *
     * @expectedDeprecation The %s::setMax() method was deprecated in %s and will be removed in %s. You must setup the class state via its __construct() method. You can still pass filter-specific options to the process() method to overwrite behavior.
     */
    public function testDeprecatedSetMaxMethod()
    {
        $this->getPostProcessorInstance()->setMax(50);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The %s::setProgressive() method was deprecated in %s and will be removed in %s. You must setup the class state via its __construct() method. You can still pass filter-specific options to the process() method to overwrite behavior.
     */
    public function testDeprecatedSetProgressiveMethod()
    {
        $this->getPostProcessorInstance()->setProgressive(50);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The %s::setStripAll() method was deprecated in %s and will be removed in %s. You must setup the class state via its __construct() method. You can still pass filter-specific options to the process() method to overwrite behavior.
     */
    public function testDeprecatedSetStripAllMethod()
    {
        $this->getPostProcessorInstance()->setStripAll(50);
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException
     * @expectedExceptionMessage the "quality" option must be an int between 0 and 100
     */
    public function testInvalidLevelOption()
    {
        $this->getSetupProcessBuilderArguments(array('quality' => 1000));
    }

    /**
     * @group legacy
     *
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Filter\PostProcessor\InvalidOptionException
     * @expectedExceptionMessage the "max" and "quality" options cannot both be set
     * @expectedDeprecation The "max" option was deprecated in %s and will be removed in %s. Instead, use the "quality" option.
     */
    public function testOptionThrowsWhenBothMaxAndQualityAreSet()
    {
        $this->getSetupProcessBuilderArguments(array('max' => 50, 'quality' => 50));
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The "max" option was deprecated in %s and will be removed in %s. Instead, use the "quality" option.
     */
    public function testInvalidStripDeprecationMessage()
    {
        $this->assertContains('--max=50', $this->getSetupProcessBuilderArguments(array('max' => 50)));
    }

    /**
     * @return mixed[]
     */
    public static function provideSetupProcessBuilderData()
    {
        $data = array(
            array(array(), array('--strip-all', '--all-progressive')),
            array(array('strip_all' => false), array('--all-progressive')),
            array(array('strip_all' => true), array('--strip-all', '--all-progressive')),
            array(array('quality' => 50), array('--strip-all', '--max=50', '--all-progressive')),
            array(array('progressive' => false), array('--strip-all', '--all-normal')),
            array(array('progressive' => true), array('--strip-all', '--all-progressive')),
       );

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
        $this->assertSame($expected, $this->getSetupProcessBuilderArguments($options));
    }

    public function testProcessWithNonSupportedMimeType()
    {
        $binary = $this->getBinaryInterfaceMock();

        $binary
            ->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturn('application/x-php');

        $this->assertSame($binary, $this->getPostProcessorInstance()->process($binary, array()));
    }

    /**
     * @return mixed[]
     */
    public static function provideProcessData()
    {
        $file = file_get_contents(__FILE__);
        $data = array(
            array(array(), '--strip-all --all-progressive'),
            array(array('strip_all' => false), '--all-progressive'),
            array(array('strip_all' => true), '--strip-all --all-progressive'),
            array(array('quality' => 50), '--strip-all --max=50 --all-progressive'),
            array(array('progressive' => false), '--strip-all --all-normal'),
            array(array('progressive' => true), '--strip-all --all-progressive'),
        );

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
     *
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     *
     * @param array  $options
     * @param string $expected
     */
    public function testProcessError($content, array $options, $expected)
    {
        $process = $this->getPostProcessorInstance(array(static::getPostProcessAsFileFailingExecutable()));
        $process->process(new Binary('content', 'image/jpeg', 'jpeg'), $options);
    }

    /**
     * @param array $parameters
     *
     * @return JpegOptimPostProcessor
     */
    protected function getPostProcessorInstance(array $parameters = array())
    {
        return new JpegOptimPostProcessor(isset($parameters[0]) ? $parameters[0] : static::getPostProcessAsFileExecutable());
    }
}
