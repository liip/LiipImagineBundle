<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Utility\OptionsResolver\OptionsResolver;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class FlipFilterLoader implements LoaderInterface
{
    /**
     * @param ImageInterface $image
     * @param array          $options
     *
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = array())
    {
        $options = $this->sanitizeOptions($options);

        return $options['axis'] === 'x' ? $image->flipHorizontally() : $image->flipVertically();
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function sanitizeOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefault('axis', 'x');
        $resolver->setAllowedValues('axis', array('x', 'horizontal', 'y', 'vertical'));
        $resolver->setNormalizer('axis', function (Options $options, $value) {
            return $value === 'horizontal' ? 'x' : ($value === 'vertical' ? 'y' : $value);
        });

        try {
            return $resolver->resolve($options);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException('The "axis" option must be set to "x", "horizontal", "y", or "vertical".');
        }
    }
}
