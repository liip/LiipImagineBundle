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
 * Be default, a metadata reader that requires the "exif" PHP extension is used. This compiler pass checks if the
 * extension is loaded or not, and switches to a metadata reader (that does not rely on "exif") if not.
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
    public function process(ContainerBuilder $container)
    {
        if (!$this->isExifExtensionLoaded() && $this->isExifMetadataReaderSet($container)) {
            $container->setDefinition(self::$metadataReaderServiceId, new Definition(self::$metadataReaderDefaultClass));
            $message = 'Overwrote "%s" service to use "%s" instead of "%s" due to missing "exif" extension '
                .'(installing the "exif" extension is highly recommended; you may experience degraded '
                .'metadata handling without it)';
            $this->log($container, $message, array(
                self::$metadataReaderServiceId,
                self::$metadataReaderDefaultClass,
                self::$metadataReaderExifClass,
            ));
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return bool
     */
    private function isExifMetadataReaderSet(ContainerBuilder $container)
    {
        return $container->getDefinition(self::$metadataReaderServiceId)->getClass() === self::$metadataReaderExifClass;
    }

    /**
     * @return bool
     */
    protected function isExifExtensionLoaded()
    {
        return extension_loaded('exif');
    }
}
