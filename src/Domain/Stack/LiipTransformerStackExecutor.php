<?php

namespace Liip\ImagineBundle\Domain\Stack;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Liip\ImagineBundle\Domain\ImageReference;
use Liip\ImagineBundle\Domain\ImageReferenceFile;
use Liip\ImagineBundle\Domain\Imagine\Filter\FilterExecutor;
use Liip\ImagineBundle\Domain\PostProcessor\PostProcessor;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class LiipTransformerStackExecutor implements TransformerStackExecutor
{
    /**
     * @var array<string, FilterExecutor>
     */
    private array $filters = [];

    /**
     * @var array<string, PostProcessor>
     */
    private array $postProcessors = [];

    public function __construct(
        private FilterConfiguration $filterConfiguration,
        private ImagineInterface $imagine,
        private MimeTypeGuesserInterface $mimeTypeGuesser,
    ) {}

    /**
     * Adds a loader to handle the given filter.
     */
    public function addFilter(string $filterName, FilterExecutor $executor): void
    {
        $this->filters[$filterName] = $executor;
    }

    /**
     * Adds a post-processor to handle binaries.
     */
    public function addPostProcessor(string $name, PostProcessor $postProcessor): void
    {
        $this->postProcessors[$name] = $postProcessor;
    }

    /**
     * Apply the stack to the image.
     */
    public function apply(string $stackName, ImageReference $sourceImageReference): ImageReference
    {
        $config = $this->filterConfiguration->get($stackName);

        $config += [
            'quality' => 100,
            'animated' => false,
        ];

        if ($sourceImageReference instanceof ImageReferenceFile) {
            $image = $this->imagine->open($sourceImageReference->getPath());
        } else {
            $image = $this->imagine->load($sourceImageReference->getContent());
        }

        $image = $this->applyFilters($image, $config);
        $resultImageReference = $this->exportConfiguredImageBinary($sourceImageReference, $image, $config);

        return $this->applyPostProcessors($resultImageReference, $config);
    }

    public function applyFilters(ImageInterface $image, array $config): ImageInterface
    {
        foreach ($this->sanitizeFilters($config['filters'] ?? []) as $name => $options) {
            $prior = $image;
            $image = $this->filters[$name]->load($image, $options);

            if ($prior !== $image) {
                $this->destroyImage($prior);
            }
        }

        return $image;
    }

    public function applyPostProcessors(ImageReference $image, array $config): ImageReference
    {
        foreach ($this->sanitizePostProcessors($config['post_processors'] ?? []) as $name => $options) {
            $image = $this->postProcessors[$name]->process($image, $options);
        }

        return $image;
    }

    private function exportConfiguredImageBinary(ImageReference $imageReference, ImageInterface $image, array $config): ImageReference
    {
        // TODO: this is for now literal copy-paste from FilterManager
        $options = [
            'quality' => $config['quality'],
        ];

        if (\array_key_exists('jpeg_quality', $config)) {
            $options['jpeg_quality'] = $config['jpeg_quality'];
        }
        if (\array_key_exists('png_compression_level', $config)) {
            $options['png_compression_level'] = $config['png_compression_level'];
        }
        if (\array_key_exists('png_compression_filter', $config)) {
            $options['png_compression_filter'] = $config['png_compression_filter'];
        }

        if ('gif' === $imageReference->getFormat() && $config['animated']) {
            $options['animated'] = $config['animated'];
        }

        $filteredFormat = $config['format'] ?? $imageReference->getFormat();
        try {
            $filteredString = $image->get($filteredFormat, $options);
        } catch (\Exception $exception) {
            // TODO: why only a problem for webp but not png/jpg? and should the stack handle this
            // we don't support converting an animated gif into webp.
            // we can't efficiently check the input data, therefore we retry with target format gif in case of an error.
            if ('webp' !== $filteredFormat || !\array_key_exists('animated', $options) || true !== $options['animated']) {
                throw $exception;
            }
            $filteredFormat = 'gif';
            $filteredString = $image->get($filteredFormat, $options);
        }

        $this->destroyImage($image);

        return new ImageReferenceInstance( // TODO
            $filteredString,
            $filteredFormat === $imageReference->getFormat() ? $imageReference->getMimeType() : $this->mimeTypeGuesser->guess($filteredString),
            $filteredFormat
        );
    }

    /**
     * Report all non-existing filters from the configuration.
     *
     * This is better than a simple array_key_exists check, which would report the issues only one at a time.
     */
    private function sanitizeFilters(array $filters): array
    {
        // TODO: is this much better than something like?
        // if ($missing = array_diff(array_keys($filters), array_keys($this->filters))) { throw new \InvalidArgumentException('missing'.implode(',', $missing)); }

        $sanitized = array_filter($filters, function (string $name): bool {
            return \array_key_exists($name, $this->filters);
        }, ARRAY_FILTER_USE_KEY);

        if (\count($filters) !== \count($sanitized)) {
            throw new \InvalidArgumentException(sprintf('Could not find filter(s): %s', implode(', ', array_map(function (string $name): string { return sprintf('"%s"', $name); }, array_diff(array_keys($filters), array_keys($sanitized))))));
        }

        return $sanitized;
    }

    /**
     * Report all non-existing post processors from the configuration.
     */
    private function sanitizePostProcessors(array $processors): array
    {
        $sanitized = array_filter($processors, function (string $name): bool {
            return \array_key_exists($name, $this->postProcessors);
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
