<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Templating;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FilterExtension extends AbstractExtension
{
    use FilterTrait;

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('imagine_filter', [$this, 'filter']),
        ];
    }
}
