<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class FormatExtensionResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var FilterConfiguration
     */
    private $filterConfig;

    public function __construct(ResolverInterface $resolver, FilterConfiguration $filterConfig)
    {
        $this->resolver = $resolver;
        $this->filterConfig = $filterConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        $path = $this->replaceExtension($path, $filter);

        return $this->resolver->resolve($path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $targetPath, $filter)
    {
        $targetPath = $this->replaceExtension($targetPath, $filter);

        return $this->resolver->store($binary, $targetPath, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function isStored($path, $filter)
    {
        $path = $this->replaceExtension($path, $filter);

        return $this->resolver->isStored($path, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
        $newPaths = [];
        foreach ($paths as $path) {
            foreach ($filters as $filter) {
                $newPath = $this->replaceExtension($path, $filter);
                if (!\in_array($newPath, $newPaths, true)) {
                    $newPaths[] = $newPath;
                }
            }
        }

        return $this->resolver->remove($newPaths, $filters);
    }

    private function replaceExtension(string $path, string $filter): string
    {
        $config = $this->filterConfig->get($filter);
        if (!$config['format']) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $path = ($extension ? mb_substr($path, 0, -mb_strlen($extension)) : $path.'.').$config['format'];

        return $path;
    }
}
