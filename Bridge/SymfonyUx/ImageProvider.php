<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Bridge\SymfonyUx;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\UX\Image\Exception\InvalidArgumentException;
use Symfony\UX\Image\Fit;
use Symfony\UX\Image\ImageTransformation;
use Symfony\UX\Image\Provider\ProviderInterface;

/**
 * Expresses a Symfony UX Image transformation as a LiipImagine runtime filter URL.
 */
final class ImageProvider implements ProviderInterface
{
    /**
     * @param string       $filterSet     the filter set the generated runtime configurations are merged onto
     * @param int          $referenceType one of the UrlGeneratorInterface constants
     * @param list<string> $formats       the formats this provider has a filter set for
     * @param bool         $autoFormat    whether the controller negotiates the format from the request
     */
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly FilterConfiguration $filterConfiguration,
        private readonly string $filterSet,
        private readonly ?string $resolver,
        private readonly int $referenceType,
        private readonly array $formats,
        private readonly bool $autoFormat
    ) {
    }

    public function getName(): string
    {
        return UxImage::PROVIDER;
    }

    public function generateUrl(ImageTransformation $transformation): string
    {
        $operations = $transformation->operations;
        $filterSet = isset($operations['filter']) ? (string) $operations['filter'] : $this->filterSet;

        return $this->cacheManager->getBrowserPath(
            $transformation->path,
            $this->resolveFilterSet($filterSet, $this->resolveFormat($transformation)),
            $this->buildRuntimeConfig($transformation),
            isset($operations['resolver']) ? (string) $operations['resolver'] : $this->resolver,
            $this->referenceType
        );
    }

    public function getSupportedOperations(): array
    {
        return ['filter', 'resolver'];
    }

    public function getSupportedFormats(): array
    {
        return $this->formats;
    }

    public function supportsAutoFormat(): bool
    {
        return $this->autoFormat;
    }

    /**
     * "auto" asks for a format negotiated per request, which is what the controller does from the Accept header.
     */
    private function resolveFormat(ImageTransformation $transformation): ?string
    {
        return null !== $transformation->format && 'auto' !== $transformation->format ? $transformation->format : null;
    }

    /**
     * @throws InvalidArgumentException if the filter set is not declared
     */
    private function resolveFilterSet(string $filterSet, ?string $format): string
    {
        $resolved = UxImage::filterSet($filterSet, $format);

        if (\array_key_exists($resolved, $this->filterConfiguration->all())) {
            return $resolved;
        }

        if (null === $format) {
            throw new InvalidArgumentException(\sprintf('The "%s" image provider renders through the "%s" filter set, which "liip_imagine.filter_sets" does not declare.', UxImage::PROVIDER, $resolved));
        }

        throw new InvalidArgumentException(\sprintf('The "%s" image provider renders the "%s" format through the "%s" filter set, which "liip_imagine.filter_sets" does not declare with "format: %s".', UxImage::PROVIDER, $format, $resolved, $format));
    }

    /**
     * We ignore quality requirements, they are not supported as runtime configuration, only globally on the filter set definition.
     *
     * @return array<string, mixed>
     */
    private function buildRuntimeConfig(ImageTransformation $transformation): array
    {
        $width = $transformation->width;
        $height = $transformation->height;

        if (null === $width && null === $height) {
            return [];
        }

        // UX Image expects a cover fit to fill the box even when the source is smaller, and "fixed" is the
        // only sizing filter that enlarges. It needs both dimensions, and crops like a cover fit does.
        if (null !== $width && null !== $height && Fit::Contain !== $transformation->fit) {
            return ['fixed' => ['width' => $width, 'height' => $height]];
        }

        // null values are removed when signing the URL - skip missing dimensions
        $size = [];
        if (null !== $width) {
            $size[0] = $width;
        }
        if (null !== $height) {
            $size[1] = $height;
        }

        return [
            'thumbnail' => [
                'size' => $size,
                'mode' => Fit::Contain === $transformation->fit ? 'inset' : 'outbound',
            ],
        ];
    }
}
