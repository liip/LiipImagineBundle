<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Form\Type;

use Liip\ImagineBundle\Form\Type\ImageType;
use Liip\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @covers \Liip\ImagineBundle\Form\Type\ImageType
 */
class ImageTypeTest extends AbstractTest
{
    protected function setUp()
    {
        if (!class_exists(AbstractType::class)) {
            $this->markTestSkipped('Requires the symfony/form package.');
        }
    }

    public function testGetParent()
    {
        $type = new ImageType();

        $this->assertSame(FileType::class, $type->getParent());
    }

    public function testConfigureOptions()
    {
        $resolver = new OptionsResolver();
        $type = new ImageType();

        $type->configureOptions($resolver);

        $this->assertTrue($resolver->isRequired('image_path'));
        $this->assertTrue($resolver->isRequired('image_filter'));

        $this->assertTrue($resolver->isDefined('image_attr'));
        $this->assertTrue($resolver->isDefined('link_url'));
        $this->assertTrue($resolver->isDefined('link_filter'));
        $this->assertTrue($resolver->isDefined('link_attr'));
    }

    public function testBuildView()
    {
        $options = [
            'image_path' => 'foo',
            'image_filter' => 'bar',
            'image_attr' => 'bazz',
            'link_url' => 'http://liip.com',
            'link_filter' => 'foo',
            'link_attr' => 'bazz',
        ];

        $view = new FormView();
        $type = new ImageType();
        $form = $this->createObjectMock(FormInterface::class);

        $type->buildView($view, $form, $options);

        foreach ($options as $name => $value) {
            $this->assertArrayHasKey($name, $view->vars);
            $this->assertSame($value, $view->vars[$name]);
        }
    }
}
