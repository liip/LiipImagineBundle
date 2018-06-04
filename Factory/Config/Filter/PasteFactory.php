<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Factory\Config\Filter;

use Liip\ImagineBundle\Config\Filter\Type\Paste;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class PasteFactory implements FilterFactoryInterface
{
    const NAME = 'auto_rotate';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options): FilterInterface
    {
        $start = [];
        if (isset($options['start'])) {
            $start = $options['start'];
        }
        return new Paste(self::NAME, $start);
    }
}
