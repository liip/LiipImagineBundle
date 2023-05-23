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
use Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper;
use Symfony\Component\Routing\RequestContext;

class NoCacheWebPathResolver implements ResolverInterface
{
    /**
     * @var RequestContext
     */
    private $requestContext;

    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    public function isStored($path, $filter)
    {
        return true;
    }

    public function resolve($path, $filter)
    {
        $port = '';
        if ('https' === $this->requestContext->getScheme() && 443 !== $this->requestContext->getHttpsPort()) {
            $port = ":{$this->requestContext->getHttpsPort()}";
        }
        if ('http' === $this->requestContext->getScheme() && 80 !== $this->requestContext->getHttpPort()) {
            $port = ":{$this->requestContext->getHttpPort()}";
        }

        return sprintf('%s://%s%s/%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $port,
            ltrim(PathHelper::filePathToUrlPath($path), '/')
        );
    }

    public function store(BinaryInterface $binary, $path, $filter)
    {
    }

    public function remove(array $paths, array $filters)
    {
    }
}
