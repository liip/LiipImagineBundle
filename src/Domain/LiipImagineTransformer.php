<?php

namespace Liip\ImagineBundle\Domain;

use Liip\ImagineBundle\Domain\Stack\TransformerStackExecutor;
use Liip\ImagineBundle\Domain\Storage\ImageLoader;
use Liip\ImagineBundle\Domain\Storage\ImageStore;

/**
 * Main entry point into the imagine system.
 *
 * The transformer takes an image id, does all necessary transformations (and potentially caching) and gives the URL to the result image.
 */
final class LiipImagineTransformer implements ImagineTransformer
{
    public function __construct(
        private ImageLoader $sourceImageLoader,
        private TransformerStackExecutor $transformerStackExecutor,
        private ImageStore $imageStore,
    ) {}

    public function transformToUrl(string $sourceImageId, string $stackName, string $targetFormat): string
    {
        if ($this->imageStore->supportsOnDemandCreation()
            || $this->imageStore->exists($sourceImageId, $stackName, $targetFormat)
        ) {
            return $this->imageStore->getUrl($sourceImageId, $stackName, $targetFormat);
        }

        $this->warmupCache($sourceImageId, [$stackName], [$targetFormat]);

        return $this->imageStore->getUrl($sourceImageId, $stackName, $targetFormat);
    }

    public function warmupCache(string $sourceImageId, array $stackNames, array $targetFormats): void
    {
        if (0 === count($stackNames)) {
            throw new \Exception('TODO: implement determining all stack names');
        }
        $sourceImage = $this->sourceImageLoader->loadImage($sourceImageId);
        foreach ($stackNames as $stackName) {
            foreach ($targetFormats as $targetFormat) {
                // TODO: if we would separate stack executor creation and execution, we could build the stack only once and apply it for each target format
                $transformedImage = $this->transformerStackExecutor->apply($stackName, $sourceImage);
                $this->imageStore->store($transformedImage, $sourceImageId, $stackName, $targetFormat);
            }
        }
    }

    public function invalidateCache(string $sourceImageId, array $stackNames = []): void
    {
        $this->imageStore->delete($sourceImageId, $stackNames);
    }
}
