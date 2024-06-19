<?php

namespace Liip\ImagineBundle\Domain\Storage;

use Liip\ImagineBundle\Domain\ImageReference;

interface ImageStore
{
    public function exists(string $imageId, string $stackName, string $targetFormat): bool;
    public function getUrl(string $imageId, string $stackName, string $targetFormat): string;
    public function store(ImageReference $image, string $imageId, string $stackName, string $targetFormat): ImageReference;

    /**
     * Delete a cached transformed image.
     *
     * Always deletes the image in all formats in which it exists.
     *
     * @param string[] $stackNames Remove cached images with specified stack names. If empty, all stacks are deleted.
     */
    public function delete(string $imageId, array $stackNames = []): void;

    /**
     * Whether this store can create the image on the fly when the URL it returns is fetched.
     *
     * (e.g. the URL points to a controller or a 404 handler is used to generate the image on demand)
     */
    public function supportsOnDemandCreation(): bool;
}
