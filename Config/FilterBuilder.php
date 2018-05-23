<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config;

use Liip\ImagineBundle\Factory\Config\FilterFactory;

final class FilterBuilder implements FilterBuilderInterface
{
    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @param FilterFactory $filterFactory
     */
    public function __construct(FilterFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * @param string $filterName
     * @param array  $filterData
     *
     * @return FilterInterface
     */
    public function build(string $filterName, array $filterData): FilterInterface
    {
        $filter = $this->filterFactory->create();
        $filter->setName($filterName);
        $filter->setOptions($filterData);

        return $filter;
    }
}
