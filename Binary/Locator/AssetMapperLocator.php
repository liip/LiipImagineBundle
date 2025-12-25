<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Locator;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Symfony\Component\AssetMapper\AssetMapperInterface;

class AssetMapperLocator implements LocatorInterface
{
    public function __construct(
        private AssetMapperInterface $assetMapper,
    ) {
    }

    public function locate(string $path): string
    {
        $path = '/'.mb_ltrim($path, '/');
        foreach ($this->assetMapper->allAssets() as $assetCandidate) {
            if ($path === $assetCandidate->publicPath) {
                return $assetCandidate->sourcePath;
            }
        }
        throw new NotLoadableException(\sprintf('Asset with public path "%s" not found.', $path));
    }
}
