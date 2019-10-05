<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Filter;

use Imagine\Gmagick\Imagine as GmagickImagine;
use Imagine\Image\ImagineInterface;
use Imagine\Imagick\Imagine as ImagickImagine;
use Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader
 */
class ResampleFilterLoaderTest extends AbstractTest
{
    /**
     * @dataProvider provideResampleData
     *
     * @param string $imgPath
     * @param float  $resolution
     */
    public function testResample($imgPath, $resolution)
    {
        $imgType = static::getSupportedDriver();
        $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('liip-imagine-bundle-test-%s-%d.%s', md5($imgPath), time(), pathinfo($imgPath, PATHINFO_EXTENSION));
        $imagine = $this->getImagineInstance($imgType);

        $image = $imagine->open($imgPath);
        $image = $this->createResampleFilterLoaderInstance($imagine)->load($image, [
            'x' => $resolution,
            'y' => $resolution,
            'unit' => 'ppc',
        ]);
        $image->save($tmpPath);

        $tmpSize = $this->getImageResolution($imgType, $tmpPath);
        @unlink($tmpPath);
        $this->assertSame(['x' => $resolution, 'y' => $resolution], $tmpSize);
    }

    /**
     * @return array[]
     */
    public static function provideResampleData()
    {
        $paths = [
            realpath(__DIR__.'/../../../Fixtures/assets/cats.png'),
            realpath(__DIR__.'/../../../Fixtures/assets/cats.jpeg'),
        ];

        $resolutions = [
            72.0,
            120.0,
            240.0,
        ];

        $data = [];
        foreach ($paths as $path) {
            foreach ($resolutions as $resolution) {
                $data[] = [$path, $resolution];
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function provideOptionsData()
    {
        return [
            [['x' => 500, 'y' => 500, 'unit' => 'ppi']],
            [['x' => 500, 'y' => 500, 'unit' => 'ppc']],
            [['x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'undefined']],
            [['x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'filter_undefined']],
            [['x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'lanczos']],
            [['x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'filter_lanczos']],
        ];
    }

    /**
     * @param array $options
     *
     * @dataProvider provideOptionsData
     */
    public function testOptions(array $options)
    {
        $image = $this->getImageInterfaceMock();
        $image->expects($this->once())
            ->method('save')
            ->willReturn($image);

        $imagine = $this->createImagineInterfaceMock();
        $imagine->expects($this->once())
            ->method('open')
            ->willReturn($image);

        $this->createResampleFilterLoaderInstance($imagine)->load($image, $options);
    }

    /**
     * @return array
     */
    public static function provideInvalidOptionsData()
    {
        return [
            [[]],
            [[
                'x' => 'string-is-invalid-type',
                'y' => 120,
                'unit' => 'ppi',
            ]],
            [[
                'x' => 120,
                'y' => ['is', 'invalid', 'type'],
                'unit' => 'ppi',
            ]],
            [[
                'x' => 120,
                'y' => 120,
                'unit' => 'invalid-value',
            ]],
        ];
    }

    /**
     * @dataProvider provideInvalidOptionsData
     */
    public function testThrowsOnInvalidOptions(array $options)
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option(s) passed to Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\ResampleFilterLoader::load().');

        $loader = $this->createResampleFilterLoaderInstance();
        $loader->load($this->getImageInterfaceMock(), $options);
    }

    public function testThrowsOnInvalidFilterOption()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for "filter" option: must be a valid constant resolvable using one of formats "\\Imagine\\Image\\ImageInterface::FILTER_%s", "\\Imagine\\Image\\ImageInterface::%s", or "%s".');

        $loader = $this->createResampleFilterLoaderInstance();
        $loader->load($this->getImageInterfaceMock(), [
            'x' => 120,
            'y' => 120,
            'unit' => 'ppi',
            'filter' => 'invalid-filter',
        ]);
    }

    public function testThrowsOnInvalidTemporaryPathOption()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp('{Unable to create temporary file in ".+" base path.}');

        $loader = $this->createResampleFilterLoaderInstance();
        $loader->load($this->getImageInterfaceMock(), [
            'x' => 120,
            'y' => 120,
            'unit' => 'ppi',
            'temp_dir' => '/this/path/does/not/exist/foo/bar/baz/qux',
        ]);
    }

    public function testThrowsOnSaveOrOpenError()
    {
        $this->expectException(\Liip\ImagineBundle\Exception\Imagine\Filter\LoadFilterException::class);

        $image = $this->getImageInterfaceMock();
        $image->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('Error saving file!'));

        $this->createResampleFilterLoaderInstance()->load($image, ['x' => 120, 'y' => 120, 'unit' => 'ppi']);
    }

    /**
     * @param ImagineInterface $imagine
     *
     * @return ResampleFilterLoader
     */
    private function createResampleFilterLoaderInstance(ImagineInterface $imagine = null)
    {
        return new ResampleFilterLoader($imagine ?: $this->createImagineInterfaceMock());
    }

    /**
     * @return string
     */
    private static function getSupportedDriver()
    {
        if (class_exists('\Imagick')) {
            return 'imagick';
        } elseif (class_exists('\Gmagick')) {
            return 'gmagick';
        }

        static::markTestSkipped('Data set requires "imagick" or "gmagick" extension to be installed and loaded.');
    }

    /**
     * @return ImagickImagine|GmagickImagine
     */
    private function getImagineInstance($driver)
    {
        switch ($driver) {
            case 'imagick':
                return new ImagickImagine();

            case 'gmagick':
            default:
                return new GmagickImagine();
        }
    }

    /**
     * @param string $driver
     * @param string $file
     *
     * @return float[]
     */
    private function getImageResolution($driver, $file)
    {
        switch ($driver) {
            case 'imagick':
                $driver = new \Imagick($file);
                break;

            case 'gmagick':
            default:
                $driver = new \Gmagick($file);
                break;
        }

        return $driver->getImageResolution();
    }
}
