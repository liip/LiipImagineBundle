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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * By default, a metadata reader based on the exif php extension is used.
 * This compiler pass checks if the extension is loaded an switches to a simpler
 * implementation if not.
 */
class MetadataReaderCompilerPass implements CompilerPassInterface
{
    const METADATA_READER_PARAM = 'liip_imagine.meta_data.reader.class';

    const DEFAULT_METADATA_READER_CLASS = 'Imagine\Image\Metadata\DefaultMetadataReader';

    const EXIF_METADATA_READER_CLASS = 'Imagine\Image\Metadata\ExifMetadataReader';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$this->extExifIsAvailable() && $this->isDefaultMetadataReader($container)) {
            $container->setParameter(self::METADATA_READER_PARAM, self::DEFAULT_METADATA_READER_CLASS);
            $this->logMetadataReaderReplaced($container);
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return bool
     */
    protected function isDefaultMetadataReader(ContainerBuilder $container)
    {
        $currentMetadataReaderParameter = $container->getParameter(self::METADATA_READER_PARAM);

        return $currentMetadataReaderParameter === self::EXIF_METADATA_READER_CLASS;
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function logMetadataReaderReplaced(ContainerBuilder $container)
    {
        $compiler = $container->getCompiler();
        $formatter = $compiler->getLoggingFormatter();
        $message = 'Automatically replaced Imagine ExifMetadataReader with DefaultMetadataReader; '.
            'you might experience issues with LiipImagineBundle; reason: PHP extension "exif" is missing; solution: '.
            'for advanced metadata extraction install the PHP extension "exif" or set a custom MetadataReader '.
            'through the "liip_imagine.meta_data.reader.class" parameter';

        $compiler->addLogMessage($formatter->format($this, $message));
    }

    /**
     * @return bool
     */
    protected function extExifIsAvailable()
    {
        return extension_loaded('exif');
    }
}
