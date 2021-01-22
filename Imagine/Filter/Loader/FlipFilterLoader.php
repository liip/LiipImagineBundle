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
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlipFilterLoader implements LoaderInterface
{
    /**
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $options = $this->sanitizeOptions($options);

        return 'x' === $options['axis'] ? $image->flipHorizontally() : $image->flipVertically();
    }

    /**
     * @return array
     */
    private function sanitizeOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefault('axis', 'x');
        $resolver->setAllowedValues('axis', ['x', 'horizontal', 'y', 'vertical']);
        $resolver->setNormalizer('axis', function (Options $options, $value) {
            return 'horizontal' === $value ? 'x' : ('vertical' === $value ? 'y' : $value);
        });

        try {
            return $resolver->resolve($options);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException('The "axis" option must be set to "x", "horizontal", "y", or "vertical".');
        }
    }
}
