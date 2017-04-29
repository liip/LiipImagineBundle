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
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
        $normalizer = function (Options $options, $value) {
            return $value === 'horizontal' ? 'x' : ($value === 'vertical' ? 'y' : $value);
        };

        $resolver = new OptionsResolver();

        /** @todo remove in v2.0 */
        if (SymfonyFramework::isKernelGreaterThanOrEqualTo('2', '7')) {
            $resolver->setDefault('axis', 'x');
            $resolver->setAllowedValues('axis', array('x', 'horizontal', 'y', 'vertical'));
            $resolver->setNormalizer('axis', $normalizer);
        } else {
            $resolver->setDefaults(array('axis' => 'x'));
            $resolver->setAllowedValues(array('axis' => array('x', 'horizontal', 'y', 'vertical')));
            $resolver->setNormalizers(array('axis' => $normalizer));
        }

        try {
            return $resolver->resolve($options);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException('The "axis" option must be set to "x", "horizontal", "y", or "vertical".');
        }
    }
}
