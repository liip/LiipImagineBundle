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

@trigger_error('The '.FilterExtension::class.' class is deprecated since version 2.7 and will be removed in 3.0; configure "twig_mode" to "lazy" instead.', E_USER_DEPRECATED);

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * @deprecated
 */
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
            new TwigFilter('imagine_filter_cache', [$this, 'filterCache']),
        ];
    }
}
