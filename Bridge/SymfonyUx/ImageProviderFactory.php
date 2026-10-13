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
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\Image\Exception\InvalidArgumentException;
use Symfony\UX\Image\Provider\AbstractProviderFactory;
use Symfony\UX\Image\Provider\Dsn;
use Symfony\UX\Image\Provider\ProviderFactoryInterface;
use Symfony\UX\Image\Provider\ProviderInterface;

/**
 * Builds an {@see ImageProvider} from a "liipimagine://<filter-set>" DSN.
 */
final class ImageProviderFactory extends AbstractProviderFactory implements ProviderFactoryInterface
{
    private const REFERENCE_TYPES = [
        'absolute_url' => UrlGeneratorInterface::ABSOLUTE_URL,
        'absolute_path' => UrlGeneratorInterface::ABSOLUTE_PATH,
        'network_path' => UrlGeneratorInterface::NETWORK_PATH,
        'relative_path' => UrlGeneratorInterface::RELATIVE_PATH,
    ];

    /**
     * @param string $defaultFilterSet the filter set used by a DSN that names none
     * @param bool   $webpGenerate     whether the controller serves a WebP variant based on the request Accept header
     */
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly FilterConfiguration $filterConfiguration,
        private readonly string $defaultFilterSet,
        private readonly bool $webpGenerate
    ) {
    }

    public function create(Dsn $dsn): ProviderInterface
    {
        $options = $this->resolveOptions($dsn);

        return new ImageProvider(
            $this->cacheManager,
            $this->filterConfiguration,
            $dsn->getHost() ?? $this->defaultFilterSet,
            $options['resolver'],
            self::REFERENCE_TYPES[$options['reference_type']],
            $options['formats'],
            $options['auto_format']
        );
    }

    protected function getSupportedSchemes(): array
    {
        return [UxImage::PROVIDER];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('resolver')
            ->default(null)
            ->allowedTypes('null', 'string');

        $resolver->define('reference_type')
            ->default('absolute_url')
            ->allowedValues(...array_keys(self::REFERENCE_TYPES));

        $resolver->define('formats')
            ->default(implode(',', UxImage::FORMATS))
            ->normalize(static function (Options $options, string $value): array {
                $formats = array_values(array_filter(array_map('trim', explode(',', $value))));

                if ([] === $formats) {
                    throw new InvalidArgumentException('Invalid "liipimagine" image provider DSN: the "formats" option must name at least one format.');
                }

                return $formats;
            });

        $resolver->define('auto_format')
            ->default($this->webpGenerate)
            ->allowedValues(true, false, 'true', 'false', '1', '0')
            ->normalize(static fn (Options $options, $value): bool => \is_bool($value) ? $value : \in_array($value, ['true', '1'], true));
    }
}
