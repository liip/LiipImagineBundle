<?php

namespace Liip\ImagineBundle\Tests\Form\Type;

use Liip\ImagineBundle\Form\Type\ImageType;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    public function testSetDefaultOptions()
    {
        $resolver = new OptionsResolver();
        $type = new ImageType();

        $type->setDefaultOptions($resolver);

        $this->assertTrue($resolver->isRequired('image_path'));
        $this->assertTrue($resolver->isRequired('image_filter'));

        $isDefinedMethod = method_exists($resolver, 'isDefined') ? 'isDefined' : 'isKnown';
        $this->assertTrue($resolver->$isDefinedMethod('image_attr'));
        $this->assertTrue($resolver->$isDefinedMethod('link_url'));
        $this->assertTrue($resolver->$isDefinedMethod('link_filter'));
        $this->assertTrue($resolver->$isDefinedMethod('link_attr'));
    }

    public function testBuildView()
    {
        $options = array(
            'image_path'   => 'foo',
            'image_filter' => 'bar',
            'image_attr'   => 'bazz',
            'link_url'     => 'http://liip.com',
            'link_filter'  => 'foo',
            'link_attr'    => 'bazz',
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
