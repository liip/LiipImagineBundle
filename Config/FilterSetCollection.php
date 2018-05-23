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

use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

final class FilterSetCollection
{
    /**
     * @var array
     */
    private $filterSets = [];

    /**
     * @var FilterConfiguration
     */
    private $filterConfiguration;

    /**
     * @var FilterSetBuilderInterface
     */
    private $filterSetBuilder;

    /**
     * @param FilterConfiguration $filterConfiguration
     * @param FilterSetBuilderInterface $filterSetBuilder
     */
    public function __construct(FilterConfiguration $filterConfiguration, FilterSetBuilderInterface $filterSetBuilder)
    {
        $this->filterConfiguration = $filterConfiguration;
        $this->filterSetBuilder = $filterSetBuilder;
    }

    /**
     * @return FilterSetInterface[]
     */
    public function getFilterSets()
    {
        if (!empty($this->filterSets)) {
            return $this->filterSets;
        }

        foreach ($this->filterConfiguration->all() as $filterSetName => $filterSetData) {
            $this->filterSets[] = $this->filterSetBuilder->build($filterSetName, $filterSetData);
        }

        return $this->filterSets;
    }
}
