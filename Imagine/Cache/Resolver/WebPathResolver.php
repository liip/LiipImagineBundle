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

use Liip\ImagineBundle\Utility\Path\PathResolverInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

class WebPathResolver extends AbstractWebPathResolver
{
    private $requestContext;

    /**
     * @param Filesystem            $filesystem
     * @param PathResolverInterface $pathResolver
     * @param RequestContext        $requestContext
     */
    public function __construct(
        Filesystem $filesystem,
        PathResolverInterface $pathResolver,
        RequestContext $requestContext
    ) {
        parent::__construct($filesystem, $pathResolver);
        $this->requestContext = $requestContext;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        return sprintf(
            '%s/%s',
            rtrim($this->getBaseUrl(), '/'),
            ltrim($this->getPathResolver()->getFileUrl($path, $filter), '/')
        );
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        $port = '';
        if ('https' === $this->requestContext->getScheme() && 443 !== $this->requestContext->getHttpsPort()) {
            $port = ":{$this->requestContext->getHttpsPort()}";
        }

        if ('http' === $this->requestContext->getScheme() && 80 !== $this->requestContext->getHttpPort()) {
            $port = ":{$this->requestContext->getHttpPort()}";
        }

        $baseUrl = $this->requestContext->getBaseUrl();
        if ('.php' === mb_substr($this->requestContext->getBaseUrl(), -4)) {
            $baseUrl = pathinfo($this->requestContext->getBaseurl(), PATHINFO_DIRNAME);
        }
        $baseUrl = rtrim($baseUrl, '/\\');

        return sprintf(
            '%s://%s%s%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $port,
            $baseUrl
        );
    }
}
