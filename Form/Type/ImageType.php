<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * ImageType.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class ImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['image_path'] = $options['image_path'];
        $view->vars['image_filter'] = $options['image_filter'];
        $view->vars['image_attr'] = $options['image_attr'];
        $view->vars['link_url'] = $options['link_url'];
        $view->vars['link_filter'] = $options['link_filter'];
        $view->vars['link_attr'] = $options['link_attr'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'image_path',
            'image_filter',
        ));

        $resolver->setDefaults(array(
            'image_attr' => array(),
            'link_url' => null,
            'link_filter' => null,
            'link_attr' => array(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'liip_imagine_image';
    }
}
