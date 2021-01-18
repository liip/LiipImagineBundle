<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

interface CacheManagerInterface
{
    public function addResolver($filter, ResolverInterface $resolver);

    public function getBrowserPath(
        $path,
        $filter,
        array $runtimeConfig = [],
        $resolver = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    );

    public function getRuntimePath($path, array $runtimeConfig);

    public function generateUrl(
        $path,
        $filter,
        array $runtimeConfig = [],
        $resolver = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    );

    public function isStored($path, $filter, $resolver = null);

    public function resolve($path, $filter, $resolver = null);

    public function store(BinaryInterface $binary, $path, $filter, $resolver = null);

    public function remove($paths = null, $filters = null);
}
