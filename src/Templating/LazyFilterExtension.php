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

final class LazyFilterExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('imagine_filter', [LazyFilterRuntime::class, 'filter']),
            new TwigFilter('imagine_filter_cache', [LazyFilterRuntime::class, 'filterCache']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'liip_imagine_lazy';
    }
}
