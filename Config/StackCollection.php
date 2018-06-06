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

final class StackCollection
{
    /**
     * @var array
     */
    private $filterSets = [];

    /**
     * @var StackBuilderInterface
     */
    private $filterSetBuilder;

    /**
     * @var array
     */
    private $filtersConfiguration;

    /**
     * @param StackBuilderInterface $filterSetBuilder
     * @param array                 $filtersConfiguration
     */
    public function __construct(StackBuilderInterface $filterSetBuilder, array $filtersConfiguration = [])
    {
        $this->filterSetBuilder = $filterSetBuilder;
        $this->filtersConfiguration = $filtersConfiguration;
    }

    /**
     * @return StackInterface[]
     */
    public function getFilterSets()
    {
        if (!empty($this->filterSets)) {
            return $this->filterSets;
        }

        foreach ($this->filtersConfiguration as $filterSetName => $filterSetData) {
            $this->filterSets[] = $this->filterSetBuilder->build($filterSetName, $filterSetData);
        }

        return $this->filterSets;
    }
}
