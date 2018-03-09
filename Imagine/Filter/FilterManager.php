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

use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\ConfigurablePostProcessorInterface;
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

    /**
     * @param FilterConfiguration      $filterConfig
     * @param ImagineInterface         $imagine
     * @param MimeTypeGuesserInterface $mimeTypeGuesser
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        ImagineInterface $imagine,
        MimeTypeGuesserInterface $mimeTypeGuesser
    ) {
        $this->filterConfig = $filterConfig;
        $this->imagine = $imagine;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
    }

    /**
     * Adds a loader to handle the given filter.
     *
     * @param string          $filter
     * @param LoaderInterface $loader
     */
    public function addLoader($filter, LoaderInterface $loader)
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * Adds a post-processor to handle binaries.
     *
     * @param string                 $name
     * @param PostProcessorInterface $postProcessor
     */
    public function addPostProcessor($name, PostProcessorInterface $postProcessor)
    {
        $this->postProcessors[$name] = $postProcessor;
    }

    /**
     * @return FilterConfiguration
     */
    public function getFilterConfiguration()
    {
        return $this->filterConfig;
    }

    /**
     * @param BinaryInterface $binary
     * @param array           $config
     *
     * @throws \InvalidArgumentException
     *
     * @return BinaryInterface
     */
    public function apply(BinaryInterface $binary, array $config)
    {
        $config = array_replace([
            'filters' => [],
            'quality' => 100,
            'animated' => false,
        ], $config);

        if ($binary instanceof FileBinaryInterface) {
            $image = $this->imagine->open($binary->getPath());
        } else {
            $image = $this->imagine->load($binary->getContent());
        }

        foreach ($config['filters'] as $eachFilter => $eachOptions) {
            if (!isset($this->loaders[$eachFilter])) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find filter loader for "%s" filter type', $eachFilter
                ));
            }

            $prevImage = $image;
            $image = $this->loaders[$eachFilter]->load($image, $eachOptions);

            // If the filter returns a different image object destruct the old one because imagick keeps consuming memory if we don't
            // See https://github.com/liip/LiipImagineBundle/pull/682
            if ($prevImage !== $image && method_exists($prevImage, '__destruct')) {
                $prevImage->__destruct();
            }
        }

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

        $filteredFormat = isset($config['format']) ? $config['format'] : $binary->getFormat();
        $filteredContent = $image->get($filteredFormat, $options);
        $filteredMimeType = $filteredFormat === $binary->getFormat() ? $binary->getMimeType() : $this->mimeTypeGuesser->guess($filteredContent);

        // We are done with the image object so we can destruct the this because imagick keeps consuming memory if we don't
        // See https://github.com/liip/LiipImagineBundle/pull/682
        if (method_exists($image, '__destruct')) {
            $image->__destruct();
        }

        return $this->applyPostProcessors(new Binary($filteredContent, $filteredMimeType, $filteredFormat), $config);
    }

    /**
     * @param BinaryInterface $binary
     * @param array           $options
     *
     * @throws \InvalidArgumentException
     *
     * @return BinaryInterface
     */
    public function applyPostProcessors(BinaryInterface $binary, $options)
    {
        foreach ($this->sanitizePostProcessors($options['post_processors'] ?? []) as $name => $config) {
            $binary = $this->postProcessors[$name]->process($binary, $config);
        }

        return $binary;
    }

    /**
     * @param array $processors
     *
     * @return array
     */
    private function sanitizePostProcessors(array $processors): array
    {
        $sanitized = array_filter($processors, function (string $name): bool {
            return isset($this->postProcessors[$name]);
        }, ARRAY_FILTER_USE_KEY);

        if (count($processors) !== count($sanitized)) {
            throw new \InvalidArgumentException(sprintf('Could not find post processor(s): %s', implode(', ', array_map(function (string $name): string {
                return sprintf('"%s"', $name);
            }, array_diff(array_keys($processors), array_keys($sanitized))))));
        }

        return $sanitized;
    }

    /**
     * Apply the provided filter set on the given binary.
     *
     * @param BinaryInterface $binary
     * @param string          $filter
     * @param array           $runtimeConfig
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
}
