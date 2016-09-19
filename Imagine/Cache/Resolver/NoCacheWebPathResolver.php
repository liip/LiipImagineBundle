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
use Symfony\Component\Routing\RequestContext;

class NoCacheWebPathResolver implements ResolverInterface
{
    /**
     * @param RequestContext $requestContext
     */
    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored($path, $filter)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        return sprintf('%s://%s/%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            ltrim($path, '/')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters)
    {
    }
}
