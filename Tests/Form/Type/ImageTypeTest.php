<?php

namespace Liip\ImagineBundle\Tests\Form\Type;

use Liip\ImagineBundle\Form\Type\ImageType;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers Liip\ImagineBundle\Form\Type\ImageType
 */
class ImageTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $type = new ImageType();

        $this->assertEquals('liip_imagine_image', $type->getName());
    }

    public function testGetParent()
    {
        $type = new ImageType();

        $this->assertEquals('file', $type->getParent());
    }

    public function testConfigureOptions()
    {
        if (version_compare(Kernel::VERSION_ID, '20600') < 0) {
            $this->markTestSkipped('No need to test on symfony < 2.6');
        }

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

    public function testLegacySetDefaultOptions()
    {
        if (version_compare(Kernel::VERSION_ID, '20600') >= 0) {
            $this->markTestSkipped('No need to test on symfony >= 2.6');
        }

        $resolver = new OptionsResolver();
        $type = new ImageType();

        $type->setDefaultOptions($resolver);

        $this->assertTrue($resolver->isRequired('image_path'));
        $this->assertTrue($resolver->isRequired('image_filter'));

        $this->assertTrue($resolver->isKnown('image_attr'));
        $this->assertTrue($resolver->isKnown('link_url'));
        $this->assertTrue($resolver->isKnown('link_filter'));
        $this->assertTrue($resolver->isKnown('link_attr'));
    }

    public function testBuildView()
    {
        $options = array(
            'image_path' => 'foo',
            'image_filter' => 'bar',
            'image_attr' => 'bazz',
            'link_url' => 'http://liip.com',
            'link_filter' => 'foo',
            'link_attr' => 'bazz',
        );

        $view = new FormView();
        $type = new ImageType();
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');

        $type->buildView($view, $form, $options);

        foreach ($options as $name => $value) {
            $this->assertArrayHasKey($name, $view->vars);
            $this->assertEquals($value, $view->vars[$name]);
        }
    }
}
