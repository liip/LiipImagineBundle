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
    private $filterSets = [];

    /**
     * @var StackBuilderInterface
     */
    private $stackBuilder;

    /**
     * @var array
     */
    private $filtersConfiguration;

    public function __construct(StackBuilderInterface $stackBuilder, array $filtersConfiguration = [])
    {
        $this->stackBuilder = $stackBuilder;
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
            $this->filterSets[] = $this->stackBuilder->build($filterSetName, $filterSetData);
        }

        return $this->filterSets;
    }
}
