<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Replaces the default exif-extension-based metadata reader with a degraded one if the exif extensions is not loaded.
 */
class MetadataReaderCompilerPass extends AbstractCompilerPass
{
    /**
     * @var string
     */
    private static $metadataReaderServiceId = 'liip_imagine.meta_data.reader';

    /**
     * @var string
     */
    private static $metadataReaderDefaultClass = 'Imagine\Image\Metadata\DefaultMetadataReader';

    /**
     * @var string
     */
    private static $metadataReaderExifClass = 'Imagine\Image\Metadata\ExifMetadataReader';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$this->isExifExtensionLoaded() && $this->isExifMetadataReaderSet($container)) {
            $container->setDefinition(self::$metadataReaderServiceId, new Definition(self::$metadataReaderDefaultClass));
            $message = 'Replaced the "%s" metadata reader service with "%s" from "%s" due to missing "exif" extension '.
                       '(as you may experience degraded metadata handling without the exif extension, installation is '.
                       'highly recommended)';
            $this->log($container, $message, self::$metadataReaderServiceId, self::$metadataReaderDefaultClass, self::$metadataReaderExifClass);
        }
    }

    protected function isExifExtensionLoaded(): bool
    {
        return \extension_loaded('exif');
    }

    private function isExifMetadataReaderSet(ContainerBuilder $container): bool
    {
        return $container->getDefinition(self::$metadataReaderServiceId)->getClass() === self::$metadataReaderExifClass;
    }
}
