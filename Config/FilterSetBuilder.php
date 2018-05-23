<?php
/**
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Config;

use Liip\ImagineBundle\Factory\Config\FilterSetFactory;

final class FilterSetBuilder implements FilterSetBuilderInterface
{
    /**
     * @var FilterSetFactory
     */
    private $filterSetFactory;

    /**
     * @var FilterBuilderInterface
     */
    private $filterBuilder;

    /**
     * @param FilterSetFactory $filterSetFactory
     * @param FilterBuilderInterface $filterBuilder
     */
    public function __construct(FilterSetFactory $filterSetFactory, FilterBuilderInterface $filterBuilder)
    {
        $this->filterSetFactory = $filterSetFactory;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param string $filterSetName
     * @param array $filterSetData
     * @return FilterSetInterface
     */
    public function build(string $filterSetName, array $filterSetData): FilterSetInterface
    {
        $filterSet = $this->filterSetFactory->create();
        $filterSet->setName($filterSetName);
        $filterSet->setDataLoader($filterSetData['data_loader']);
        $filterSet->setQuality($filterSetData['quality']);

        if (!empty($filterSetData['filters'])) {
            $filters = [];
            foreach ($filterSetData['filters'] as $filterName => $filterData) {
                $filters[] = $this->filterBuilder->build($filterName, $filterData);
            }
            $filterSet->setFilters($filters);
        }

        return $filterSet;
    }
}
