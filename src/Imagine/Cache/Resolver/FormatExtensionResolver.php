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
    private ResolverInterface $resolver;

    private FilterConfiguration $filterConfig;

    public function __construct(ResolverInterface $resolver, FilterConfiguration $filterConfig)
    {
        $this->resolver = $resolver;
        $this->filterConfig = $filterConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $path, string $filter): string
    {
        $path = $this->replaceExtension($path, $filter);

        return $this->resolver->resolve($path, $filter);
    }

    public function store(BinaryInterface $binary, string $path, string $filter): void
    {
        $path = $this->replaceExtension($path, $filter);

        $this->resolver->store($binary, $path, $filter);
    }

    public function isStored(string $path, string $filter): bool
    {
        $path = $this->replaceExtension($path, $filter);

        return $this->resolver->isStored($path, $filter);
    }

    public function remove(array $paths, array $filters): void
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

        $this->resolver->remove($newPaths, $filters);
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
