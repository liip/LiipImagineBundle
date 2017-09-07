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

use Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Liip\ImagineBundle\Model\FileBinary;
use Symfony\Component\Finder\Finder;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor
 */
class AbstractPostProcessorTest extends AbstractPostProcessorTestCase
{
    /**
     * @group legacy
     *
     * @expectedDeprecation Calling the %s::process() method without a second parameter of options was deprecated in %s and will be removed in %s.
     */
    public function testProcessDeprecation()
    {
        $this
            ->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'process')
            ->invoke($processor, $this->getBinaryInterfaceMock());
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation The %s::processWithConfiguration() method was deprecated in %s and will be removed in %s. Use the %s::process() method instead.
     */
    public function testProcessWithConfigurationDeprecation()
    {
        $this
            ->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'processWithConfiguration')
            ->invoke($processor, $this->getBinaryInterfaceMock(), array());
    }

    public function testIsBinaryOfType()
    {
        $binary = $this->getBinaryInterfaceMock();

        $binary
            ->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturnOnConsecutiveCalls(
                'image/jpg', 'image/jpeg', 'text/plain', 'image/png', 'image/jpg', 'image/jpeg', 'text/plain', 'image/png'
            );

        $processor = $this->getPostProcessorInstance();

        $m = $this->getProtectedReflectionMethodVisible($processor, 'isBinaryTypeJpgImage');
        $this->assertTrue($m->invoke($processor, $binary));
        $this->assertTrue($m->invoke($processor, $binary));
        $this->assertFalse($m->invoke($processor, $binary));
        $this->assertFalse($m->invoke($processor, $binary));

        $m = $this->getProtectedReflectionMethodVisible($processor, 'isBinaryTypePngImage');
        $this->assertFalse($m->invoke($processor, $binary));
        $this->assertFalse($m->invoke($processor, $binary));
        $this->assertFalse($m->invoke($processor, $binary));
        $this->assertTrue($m->invoke($processor, $binary));
    }

    public function testCreateProcessBuilder()
    {
        $optionTimeout = 120.0;
        $optionPrefix = array('a-custom-prefix');
        $optionWorkDir = getcwd();
        $optionEnvVars = array('FOO' => 'BAR');
        $optionOptions = array('bypass_shell' => true);

        $m = $this->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'createProcessBuilder');
        $b = $m->invokeArgs($processor, array(array('/path/to/bin'), array(
            'process' => array(
                'timeout' => $optionTimeout,
                'prefix' => $optionPrefix,
                'working_directory' => $optionWorkDir,
                'environment_variables' => $optionEnvVars,
                'options' => $optionOptions,
            ),
        )));

        $this->assertSame($optionTimeout, $this->getProtectedReflectionPropertyVisible($b, 'timeout')->getValue($b));
        $this->assertSame($optionPrefix, $this->getProtectedReflectionPropertyVisible($b, 'prefix')->getValue($b));
        $this->assertSame($optionWorkDir, $this->getProtectedReflectionPropertyVisible($b, 'cwd')->getValue($b));
        $this->assertSame($optionEnvVars, $this->getProtectedReflectionPropertyVisible($b, 'env')->getValue($b));
        $this->assertSame($optionOptions, $this->getProtectedReflectionPropertyVisible($b, 'options')->getValue($b));
    }

    /**
     * @return array[]
     */
    public static function provideWriteTemporaryFileData()
    {
        $find = new Finder();
        $data = array();

        foreach ($find->in(__DIR__)->name('*.php')->files() as $f) {
            $data[] = array(file_get_contents($f), 'application/x-php', 'php', 'foo-context', array());
            $data[] = array(file_get_contents($f), 'application/x-php', 'php', 'bar-context', array('temp_dir' => null));
            $data[] = array(file_get_contents($f), 'application/x-php', 'php', 'bar-context', array('temp_dir' => sys_get_temp_dir()));
            $data[] = array(file_get_contents($f), 'application/x-php', 'php', 'baz-context', array('temp_dir' => sprintf('%s/foo/bar/baz', sys_get_temp_dir())));
        }

        return $data;
    }

    /**
     * @dataProvider provideWriteTemporaryFileData
     *
     * @param string $content
     * @param string $mimeType
     * @param string $format
     * @param string $prefix
     * @param array  $options
     */
    public function testWriteTemporaryFile($content, $mimeType, $format, $prefix, array $options)
    {
        $writer = $this->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'writeTemporaryFile');

        $baseBinary = new Binary($content, $mimeType, $format);
        $this->assertTemporaryFile($content, $base = $writer->invoke($processor, $baseBinary, $options, $prefix), $prefix, $options);

        $fileBinary = new FileBinary($base, $mimeType, $format);
        $this->assertTemporaryFile($content, $file = $writer->invoke($processor, $fileBinary, $options, $prefix), $prefix, $options);

        @unlink($base);
        @unlink($file);

        if (is_dir($dir = sprintf('%s/foo/bar/baz', sys_get_temp_dir()))) {
            @rmdir($dir);
        }

        if (is_dir($dir = sprintf('%s/foo/bar', sys_get_temp_dir()))) {
            @rmdir($dir);
        }

        if (is_dir($dir = sprintf('%s/foo', sys_get_temp_dir()))) {
            @rmdir($dir);
        }
    }

    /**
     * @return array[]
     */
    public static function provideIsValidReturnData()
    {
        return array(
            array(array(), array(), true),
            array(array(0), array(), true),
            array(array(100, 200, 0), array(), true),
            array(array(100), array(), false),
            array(array(100, 200), array(), false),
            array(array(), array('ERROR'), true),
            array(array(0), array('foo'), false),
            array(array(0), array('foo-bar', 'baz'), false),
            array(array(0), array('foo-bar', 'ERROR'), true),
        );
    }

    /**
     * @dataProvider provideIsValidReturnData
     *
     * @param array $validReturns
     * @param array $errorString
     * @param bool  $expected
     */
    public function testIsValidReturn(array $validReturns, array $errorString, $expected)
    {
        $process = $this
            ->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $process
            ->expects($this->any())
            ->method('getExitCode')
            ->willReturn(0);

        $process
            ->expects($this->any())
            ->method('getOutput')
            ->willReturn('foo bar baz');

        $result = $this
            ->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'isSuccessfulProcess')
            ->invoke($processor, $process, $validReturns, $errorString);

        $this->assertSame($expected, $result);
    }

    /**
     * @param array $parameters
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractPostProcessor
     */
    protected function getPostProcessorInstance(array $parameters = array())
    {
        if (count($parameters) === 0) {
            $parameters = array(static::getPostProcessAsStdInExecutable());
        }

        return $this
            ->getMockBuilder('\Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor')
            ->setConstructorArgs($parameters)
            ->getMockForAbstractClass();
    }
}
