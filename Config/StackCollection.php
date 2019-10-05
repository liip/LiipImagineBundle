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
    private $stacks = [];

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
    public function getStacks()
    {
        if (!empty($this->stacks)) {
            return $this->stacks;
        }

        foreach ($this->filtersConfiguration as $filterSetName => $filterSetData) {
            $this->stacks[] = $this->stackBuilder->build($filterSetName, $filterSetData);
        }

        return $this->stacks;
    }
}
