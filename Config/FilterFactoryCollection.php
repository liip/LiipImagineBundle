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

use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Liip\ImagineBundle\Factory\Config\FilterFactoryInterface;

final class FilterFactoryCollection implements FilterFactoryCollectionInterface
{
    /**
     * @var FilterFactoryInterface[]
     */
    private $filterFactories;

    /**
     * FilterCollection constructor.
     * @param FilterFactoryInterface ...$filterFactories
     */
    public function __construct(FilterFactoryInterface ...$filterFactories)
    {
        $this->filterFactories = $filterFactories;
    }

    /**
     * @param string $name
     * @return FilterFactoryInterface
     * @throws NotFoundException
     */
    public function getFilterFactoryByName(string $name): FilterFactoryInterface
    {
        foreach ($this->filterFactories as $filterFactory) {
            if ($filterFactory->getName() === $name) {
                return $filterFactory;
            }
        }

        throw new NotFoundException(sprintf("Filter factory with name '%s' was not found.", $name));
    }

    /**
     * @return FilterFactoryInterface[]
     */
    public function getAll()
    {
        return $this->filterFactories;
    }
}
