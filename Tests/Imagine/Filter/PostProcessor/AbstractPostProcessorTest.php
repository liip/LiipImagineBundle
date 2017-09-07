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

    public function testCreateProcess()
    {
        $optionTimeout = 120.0;
        $optionWorkDir = getcwd();
        $optionEnvVars = ['FOO' => 'BAR'];

        $m = $this->getProtectedReflectionMethodVisible($processor = $this->getPostProcessorInstance(), 'createProcess');
        $b = $m->invokeArgs($processor, [['/path/to/bin'], [
            'process' => [
                'timeout' => $optionTimeout,
                'working_directory' => $optionWorkDir,
                'environment_variables' => $optionEnvVars,
            ],
        ]]);

        $this->assertSame($optionTimeout, $this->getProtectedReflectionPropertyVisible($b, 'timeout')->getValue($b));
        $this->assertSame($optionWorkDir, $this->getProtectedReflectionPropertyVisible($b, 'cwd')->getValue($b));
        $this->assertSame($optionEnvVars, $this->getProtectedReflectionPropertyVisible($b, 'env')->getValue($b));
    }

    /**
     * @return array[]
     */
    public static function provideWriteTemporaryFileData()
    {
        $find = new Finder();
        $data = [];

        foreach ($find->in(__DIR__)->name('*.php')->files() as $f) {
            $data[] = [file_get_contents($f), 'application/x-php', 'php', 'foo-context', []];
            $data[] = [file_get_contents($f), 'application/x-php', 'php', 'bar-context', ['temp_dir' => null]];
            $data[] = [file_get_contents($f), 'application/x-php', 'php', 'bar-context', ['temp_dir' => sys_get_temp_dir()]];
            $data[] = [file_get_contents($f), 'application/x-php', 'php', 'baz-context', ['temp_dir' => sprintf('%s/foo/bar/baz', sys_get_temp_dir())]];
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
        return [
            [[], [], true],
            [[0], [], true],
            [[100, 200, 0], [], true],
            [[100], [], false],
            [[100, 200], [], false],
            [[], ['ERROR'], true],
            [[0], ['foo'], false],
            [[0], ['foo-bar', 'baz'], false],
            [[0], ['foo-bar', 'ERROR'], true],
        ];
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
    protected function getPostProcessorInstance(array $parameters = [])
    {
        if (count($parameters) === 0) {
            $parameters = [static::getPostProcessAsStdInExecutable()];
        }

        return $this
            ->getMockBuilder('\Liip\ImagineBundle\Imagine\Filter\PostProcessor\AbstractPostProcessor')
            ->setConstructorArgs($parameters)
            ->getMockForAbstractClass();
    }
}
