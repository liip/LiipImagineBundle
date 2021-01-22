<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Factory\Config\Filter\Argument;

use Liip\ImagineBundle\Config\Filter\Argument\Size;
use Liip\ImagineBundle\Exception\InvalidArgumentException;

/**
 * @internal
 * @codeCoverageIgnore
 */
final class SizeFactory
{
    public function create(int $width = null, int $height = null): Size
    {
        return new Size($width, $height);
    }

    public function createFromOptions(array $options, string $propertyName = 'size'): Size
    {
        if (!isset($options[$propertyName])) {
            return new Size();
        }

        if (!\is_array($options[$propertyName])) {
            throw new InvalidArgumentException(sprintf('Invalid value for %s provided, array expected.', $propertyName));
        }

        $width = $options[$propertyName][0] ?? null;
        $height = $options[$propertyName][1] ?? null;

        return new Size($width, $height);
    }
}
