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
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class responsible for locating assets in the asset mapper.
 * Implements the LocatorInterface to resolve asset paths.
 * This Class should be used only in dev environment.
 */
class AssetMapperLocator implements LocatorInterface
{

	private $assetMapper;
	private $cacheMapCache = null;

	public function __construct(
		AssetMapperInterface $assetMapper,
		?CacheItemPoolInterface $cacheMapCache = null
	) {
		$this->cacheMapCache = $cacheMapCache;
		$this->assetMapper   = $assetMapper;
	}

	/**
	 * Locates an asset by its public path.
	 *
	 * This method attempts to retrieve an asset using its public path. It first
	 * checks for a cached version of the asset, and if none is found, it iterates
	 * through all available assets to find a match. If no matching asset is located,
	 * an exception is thrown.
	 *
	 * Inspired by Symfony's AssetMapper component @see https://github.com/symfony/asset-mapper/blob/7.3/AssetMapperDevServerSubscriber.php#L179.
	 *
	 * @param string $path The public path of the asset to locate.
	 *
	 * @return string The located asset.
	 *
	 * @throws NotLoadableException If no asset with the specified public path is found.
	 */
	public function locate(string $path): string
	{
		$pathInfo = '/'.ltrim($path, '/');
		$cachedAsset = null;
		if (null !== $this->cacheMapCache) {
			$cachedAsset = $this->cacheMapCache->getItem(hash('xxh128', $pathInfo));
			$asset = $cachedAsset->isHit() ? $this->assetMapper->getAsset($cachedAsset->get()) : null;

			if (null !== $asset && $asset->publicPath === $pathInfo) {
				return $asset->sourcePath;
			}
		}

		// we did not find a match
		$asset = null;
		foreach ($this->assetMapper->allAssets() as $assetCandidate) {
			if ($pathInfo === $assetCandidate->publicPath) {
				$asset = $assetCandidate;
				break;
			}
		}

		if (null === $asset) {
			throw new NotLoadableException(\sprintf('Asset with public path "%s" not found.', $pathInfo));
		}

		if (null !== $cachedAsset) {
			$cachedAsset->set($asset->logicalPath);
			$this->cacheMapCache->save($cachedAsset);
		}

		return $asset->sourcePath;
	}
}
