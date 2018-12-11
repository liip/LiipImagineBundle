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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $resolver->setRequired([
            'image_path',
            'image_filter',
        ]);

        $resolver->setDefaults([
            'image_attr' => [],
            'link_url' => null,
            'link_filter' => null,
            'link_attr' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'liip_imagine_image';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FileType::class;
    }
}
