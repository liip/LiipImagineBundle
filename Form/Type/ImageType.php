<?php

namespace Liip\ImagineBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * ImageType provides ability upload file and display preview of image.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class ImageType extends AbstractType
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $imagePath = $options['image_path'];
        if ($options['image_path_property_path']) {
            $imagePath = $this->propertyAccessor->getValue($options['data'], $options['image_path_property_path']);
        }

        $view->vars['image_path'] = $imagePath;
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
            'image_filter',
        ));

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setDefined(array('image_path'));
        } else { // BC layer for Symfony < 2.6
            $resolver->setOptional(array('image_path'));
        }

        $resolver->setDefaults(array(
            'image_path_property_path' => null,
            'image_attr' => array(),
            'link_url' => null,
            'link_filter' => null,
            'link_attr' => array(),
        ));

        $normalizer = function (Options $options, $value) {
            if (null !== $value) {
                $options['image_path'] = null;

                return $value;
            } elseif (!isset($options['image_path'])) {
                throw new MissingOptionsException('You should provide "image_path" or "image_path_property_path" option value.');
            }
        };

        if (method_exists($resolver, 'setNormalizer')) {
            $resolver->setNormalizer('image_path_property_path', $normalizer);
        } else { // BC layer for Symfony < 2.6
            $resolver->setNormalizers(array('image_path_property_path', $normalizer));
        }

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
