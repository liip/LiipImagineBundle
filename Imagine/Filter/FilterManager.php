<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Model\Binary;

class FilterManager
{
    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = [];

    /**
     * @var PostProcessorInterface[]
     */
    protected $postProcessors = [];

    public function __construct(FilterConfiguration $filterConfig, ImagineInterface $imagine, MimeTypeGuesserInterface $mimeTypeGuesser)
    {
        $this->filterConfig = $filterConfig;
        $this->imagine = $imagine;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
    }

    /**
     * Adds a loader to handle the given filter.
     */
    public function addLoader(string $filter, LoaderInterface $loader): void
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * Adds a post-processor to handle binaries.
     */
    public function addPostProcessor(string $name, PostProcessorInterface $postProcessor): void
    {
        $this->postProcessors[$name] = $postProcessor;
    }

    public function getFilterConfiguration(): FilterConfiguration
    {
        return $this->filterConfig;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function apply(BinaryInterface $binary, array $config): BinaryInterface
    {
        $config += [
            'quality' => 100,
            'animated' => false,
        ];

        return $this->applyPostProcessors($this->applyFilters($binary, $config), $config);
    }

    public function applyFilters(BinaryInterface $binary, array $config): BinaryInterface
    {
        if ($binary instanceof FileBinaryInterface) {
            $image = $this->imagine->open($binary->getPath());
        } else {
            $image = $this->imagine->load($binary->getContent());
        }

        foreach ($this->sanitizeFilters($config['filters'] ?? []) as $name => $options) {
            $prior = $image;
            $image = $this->loaders[$name]->load($image, $options);

            if ($prior !== $image) {
                $this->destroyImage($prior);
            }
        }

        return $this->exportConfiguredImageBinary($binary, $image, $config);
    }

    /**
     * Apply the provided filter set on the given binary.
     *
     * @param string $filter
     *
     * @throws \InvalidArgumentException
     *
     * @return BinaryInterface
     */
    public function applyFilter(BinaryInterface $binary, $filter, array $runtimeConfig = [])
    {
        $config = array_replace_recursive(
            $this->getFilterConfiguration()->get($filter),
            $runtimeConfig
        );

        return $this->apply($binary, $config);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function applyPostProcessors(BinaryInterface $binary, array $config): BinaryInterface
    {
        foreach ($this->sanitizePostProcessors($config['post_processors'] ?? []) as $name => $options) {
            $binary = $this->postProcessors[$name]->process($binary, $options);
        }

        return $binary;
    }

    private function exportConfiguredImageBinary(BinaryInterface $binary, ImageInterface $image, array $config): BinaryInterface
    {
        $options = [
            'quality' => $config['quality'],
        ];

        if (isset($config['jpeg_quality'])) {
            $options['jpeg_quality'] = $config['jpeg_quality'];
        }
        if (isset($config['png_compression_level'])) {
            $options['png_compression_level'] = $config['png_compression_level'];
        }
        if (isset($config['png_compression_filter'])) {
            $options['png_compression_filter'] = $config['png_compression_filter'];
        }

        if ('gif' === $binary->getFormat() && $config['animated']) {
            $options['animated'] = $config['animated'];
        }

        $filteredFormat = $config['format'] ?? $binary->getFormat();
        $filteredString = $image->get($filteredFormat, $options);

        $this->destroyImage($image);

        return new Binary(
            $filteredString,
            $filteredFormat === $binary->getFormat() ? $binary->getMimeType() : $this->mimeTypeGuesser->guess($filteredString),
            $filteredFormat
        );
    }

    private function sanitizeFilters(array $filters): array
    {
        $sanitized = array_filter($filters, function (string $name): bool {
            return isset($this->loaders[$name]);
        }, ARRAY_FILTER_USE_KEY);

        if (\count($filters) !== \count($sanitized)) {
            throw new \InvalidArgumentException(sprintf('Could not find filter(s): %s', implode(', ', array_map(function (string $name): string { return sprintf('"%s"', $name); }, array_diff(array_keys($filters), array_keys($sanitized))))));
        }

        return $sanitized;
    }

    private function sanitizePostProcessors(array $processors): array
    {
        $sanitized = array_filter($processors, function (string $name): bool {
            return isset($this->postProcessors[$name]);
        }, ARRAY_FILTER_USE_KEY);

        if (\count($processors) !== \count($sanitized)) {
            throw new \InvalidArgumentException(sprintf('Could not find post processor(s): %s', implode(', ', array_map(function (string $name): string { return sprintf('"%s"', $name); }, array_diff(array_keys($processors), array_keys($sanitized))))));
        }

        return $sanitized;
    }

    /**
     * We are done with the image object so we can destruct the this because imagick keeps consuming memory if we don't.
     * See https://github.com/liip/LiipImagineBundle/pull/682
     */
    private function destroyImage(ImageInterface $image): void
    {
        if (method_exists($image, '__destruct')) {
            $image->__destruct();
        }
    }
}
