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

class RelativeWebPathResolver extends AbstractWebPathResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        return sprintf('/%s', $this->getPathResolver()->getFileUrl($path, $filter));
    }
}
