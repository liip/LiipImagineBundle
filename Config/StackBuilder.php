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

use Liip\ImagineBundle\Factory\Config\StackFactoryInterface;

final class StackBuilder implements StackBuilderInterface
{
    /**
     * @var StackFactoryInterface
     */
    private $stackFactory;

    /**
     * @var FilterFactoryCollection
     */
    private $filterFactoryCollection;

    public function __construct(StackFactoryInterface $stackFactory, FilterFactoryCollection $filterFactoryCollection)
    {
        $this->stackFactory = $stackFactory;
        $this->filterFactoryCollection = $filterFactoryCollection;
    }

    public function build(string $stackName, array $stackData): StackInterface
    {
        $filters = [];
        if (!empty($stackData['filters'])) {
            foreach ($stackData['filters'] as $filterName => $filterData) {
                $filterFactory = $this->filterFactoryCollection->getFilterFactoryByName($filterName);
                $filters[] = $filterFactory->create($filterData);
            }
        }

        return $this->stackFactory->create(
            $stackName,
            $stackData['data_loader'],
            $stackData['quality'],
            $filters
        );
    }
}
