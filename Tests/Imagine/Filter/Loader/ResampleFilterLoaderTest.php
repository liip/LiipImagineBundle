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

use Imagine\Imagick\Imagine;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader
 */
class ResampleFilterLoaderTest extends AbstractTest
{
    public function testResample()
    {
        if (!class_exists('\Imagick')) {
            $this->markTestSkipped('Requires \Imagick class (`imagick` extension) to be available.');
        }

        $imgPath = realpath(__DIR__.'/../../../Fixtures/assets/cats.png');
        $tmpPath = tempnam(sys_get_temp_dir(), 'liip-imagine-bundle-test');
        $imagine = new Imagine();
        $ppc     = 240.0;

        $image = $imagine->open($imgPath);
        $image = $this->createResampleFilterLoaderInstance($imagine)->load($image, array(
            'x' => $ppc,
            'y' => $ppc,
            'unit' => 'ppc',
        ));
        $image->save($tmpPath);

        $imagick = new \Imagick($tmpPath);
        $this->assertSame(array('x' => $ppc, 'y' => $ppc), $imagick->getImageResolution());

        @unlink($tmpPath);
    }

    /**
     * @return array
     */
    public static function provideOptionsData()
    {
        return array(
            array(array('x' => 500, 'y' => 500, 'unit' => 'ppi')),
            array(array('x' => 500, 'y' => 500, 'unit' => 'ppc')),
            array(array('x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'undefined')),
            array(array('x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'filter_undefined')),
            array(array('x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'lanczos')),
            array(array('x' => 120, 'y' => 120, 'unit' => 'ppi', 'filter' => 'filter_lanczos')),
        );
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
        return array(
            array(array()),
            array(array(
                'x' => 'string-is-invalid-type',
                'y' => 120,
                'unit' => 'ppi',
            )),
            array(array(
                'x' => 120,
                'y' => array('is', 'invalid', 'type'),
                'unit' => 'ppi',
            )),
            array(array(
                'x' => 120,
                'y' => 120,
                'unit' => 'invalid-value',
            )),
        );
    }

    /**
     * @dataProvider provideInvalidOptionsData
     *
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid option(s) passed to Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader::load().
     */
    public function testThrowsOnInvalidOptions(array $options)
    {
        $loader = $this->createResampleFilterLoaderInstance();
        $loader->load($this->getImageInterfaceMock(), $options);
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid value for "filter" option: must be a valid constant resolvable using one of formats "\Imagine\Image\ImageInterface::FILTER_%s", "\Imagine\Image\ImageInterface::%s", or "%s".
     */
    public function testThrowsOnInvalidFilterOption()
    {
        $loader = $this->createResampleFilterLoaderInstance();
        $loader->load($this->getImageInterfaceMock(), array(
            'x' => 120,
            'y' => 120,
            'unit' => 'ppi',
            'filter' => 'invalid-filter',
        ));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp {Unable to create temporary file in ".+" base path.}
     */
    public function testThrowsOnInvalidTemporaryPathOption()
    {
        $loader = $this->createResampleFilterLoaderInstance();
        $loader->load($this->getImageInterfaceMock(), array(
            'x' => 120,
            'y' => 120,
            'unit' => 'ppi',
            'temp_dir' => '/this/path/does/not/exist/foo/bar/baz/qux',
        ));
    }

    /**
     * @expectedException \Liip\ImagineBundle\Exception\Imagine\Filter\LoadFilterException
     */
    public function testThrowsOnSaveOrOpenError()
    {
        $image = $this->getImageInterfaceMock();
        $image->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('Error saving file!'));

        $this->createResampleFilterLoaderInstance()->load($image, array('x' => 120, 'y' => 120, 'unit' => 'ppi'));
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
}
