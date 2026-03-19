<?php

declare(strict_types=1);

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Imagine\Gd\Imagine;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Metadata\ExifMetadataReader;
use Liip\ImagineBundle\Binary\Loader\ChainLoader;
use Liip\ImagineBundle\Binary\Loader\FileSystemLoader;
use Liip\ImagineBundle\Binary\Loader\FlysystemLoader;
use Liip\ImagineBundle\Binary\Loader\FlysystemV2Loader;
use Liip\ImagineBundle\Binary\Loader\StreamLoader;
use Liip\ImagineBundle\Binary\Locator\AssetMapperLocator;
use Liip\ImagineBundle\Binary\Locator\FileSystemInsecureLocator;
use Liip\ImagineBundle\Binary\Locator\FileSystemLocator;
use Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser;
use Liip\ImagineBundle\Config\Controller\ControllerConfig;
use Liip\ImagineBundle\Config\FilterFactoryCollection;
use Liip\ImagineBundle\Config\StackBuilder;
use Liip\ImagineBundle\Config\StackCollection;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\PointFactory;
use Liip\ImagineBundle\Factory\Config\Filter\Argument\SizeFactory;
use Liip\ImagineBundle\Factory\Config\Filter\AutoRotateFactory;
use Liip\ImagineBundle\Factory\Config\Filter\BackgroundFactory;
use Liip\ImagineBundle\Factory\Config\Filter\CropFactory;
use Liip\ImagineBundle\Factory\Config\Filter\DownscaleFactory;
use Liip\ImagineBundle\Factory\Config\Filter\FlipFactory;
use Liip\ImagineBundle\Factory\Config\Filter\GrayscaleFactory;
use Liip\ImagineBundle\Factory\Config\Filter\InterlaceFactory;
use Liip\ImagineBundle\Factory\Config\Filter\PasteFactory;
use Liip\ImagineBundle\Factory\Config\Filter\RelativeResizeFactory;
use Liip\ImagineBundle\Factory\Config\Filter\ResizeFactory;
use Liip\ImagineBundle\Factory\Config\Filter\RotateFactory;
use Liip\ImagineBundle\Factory\Config\Filter\ScaleFactory;
use Liip\ImagineBundle\Factory\Config\Filter\StripFactory;
use Liip\ImagineBundle\Factory\Config\Filter\ThumbnailFactory;
use Liip\ImagineBundle\Factory\Config\Filter\UpscaleFactory;
use Liip\ImagineBundle\Factory\Config\Filter\WatermarkFactory;
use Liip\ImagineBundle\Factory\Config\StackFactory;
use Liip\ImagineBundle\Form\Type\ImageType;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\CacheResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemV2Resolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\PsrCacheResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Liip\ImagineBundle\Imagine\Cache\Signer;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\BackgroundFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\FixedFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\FlipFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\GrayscaleFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\PasteFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\RelativeResizeFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\ResampleFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\ScaleFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\StripFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\UpscaleFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\Loader\WatermarkFilterLoader;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\CwebpPostProcessor;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\MozJpegPostProcessor;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\OptiPngPostProcessor;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PngquantPostProcessor;
use Liip\ImagineBundle\Service\FilterService;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    // JpegOptim parameters
    $parameters->set('liip_imagine.jpegoptim.binary', '/usr/bin/jpegoptim');
    $parameters->set('liip_imagine.jpegoptim.stripAll', true);
    $parameters->set('liip_imagine.jpegoptim.max', null);
    $parameters->set('liip_imagine.jpegoptim.progressive', true);
    $parameters->set('liip_imagine.jpegoptim.tempDir', null);

    // OptiPng parameters
    $parameters->set('liip_imagine.optipng.binary', '/usr/bin/optipng');
    $parameters->set('liip_imagine.optipng.level', 7);
    $parameters->set('liip_imagine.optipng.stripAll', true);
    $parameters->set('liip_imagine.optipng.tempDir', null);

    // Pngquant parameters
    $parameters->set('liip_imagine.pngquant.binary', '/usr/bin/pngquant');

    // MozJpeg parameters
    $parameters->set('liip_imagine.mozjpeg.binary', '/opt/mozjpeg/bin/cjpeg');

    // cwebp parameters
    $parameters->set('liip_imagine.cwebp.binary', '/usr/bin/cwebp');
    $parameters->set('liip_imagine.cwebp.tempDir', null);
    $parameters->set('liip_imagine.cwebp.q', 75);
    $parameters->set('liip_imagine.cwebp.alphaQ', 100);
    $parameters->set('liip_imagine.cwebp.m', 4);
    $parameters->set('liip_imagine.cwebp.alphaFilter', 'fast');
    $parameters->set('liip_imagine.cwebp.alphaMethod', 1);
    $parameters->set('liip_imagine.cwebp.exact', false);
    $parameters->set('liip_imagine.cwebp.metadata', ['none']);

    // Factory services
    $services->set('liip_imagine.factory.config.filter.argument.point', PointFactory::class);

    $services->set('liip_imagine.factory.config.filter.argument.size', SizeFactory::class);

    $services->set('liip_imagine.factory.config.stack', StackFactory::class);

    $services->set('liip_imagine.factory.config.filter.auto_rotate', AutoRotateFactory::class);

    $services->set('liip_imagine.factory.config.filter.background', BackgroundFactory::class)
        ->args([service('liip_imagine.factory.config.filter.argument.size')]);

    $services->set('liip_imagine.factory.config.filter.crop', CropFactory::class)
        ->args([
            service('liip_imagine.factory.config.filter.argument.size'),
            service('liip_imagine.factory.config.filter.argument.point'),
        ]);

    $services->set('liip_imagine.factory.config.filter.downscale', DownscaleFactory::class)
        ->args([service('liip_imagine.factory.config.filter.argument.size')]);

    $services->set('liip_imagine.factory.config.filter.flip', FlipFactory::class);

    $services->set('liip_imagine.factory.config.filter.grayscale', GrayscaleFactory::class);

    $services->set('liip_imagine.factory.config.filter.interlace', InterlaceFactory::class);

    $services->set('liip_imagine.factory.config.filter.paste', PasteFactory::class)
        ->args([service('liip_imagine.factory.config.filter.argument.point')]);

    $services->set('liip_imagine.factory.config.filter.relative_resize', RelativeResizeFactory::class);

    $services->set('liip_imagine.factory.config.filter.resize', ResizeFactory::class)
        ->args([service('liip_imagine.factory.config.filter.argument.size')]);

    $services->set('liip_imagine.factory.config.filter.rotate', RotateFactory::class);

    $services->set('liip_imagine.factory.config.filter.scale', ScaleFactory::class)
        ->args([service('liip_imagine.factory.config.filter.argument.size')]);

    $services->set('liip_imagine.factory.config.filter.strip', StripFactory::class);

    $services->set('liip_imagine.factory.config.filter.thumbnail', ThumbnailFactory::class)
        ->args([service('liip_imagine.factory.config.filter.argument.size')]);

    $services->set('liip_imagine.factory.config.filter.upscale', UpscaleFactory::class)
        ->args([service('liip_imagine.factory.config.filter.argument.size')]);

    $services->set('liip_imagine.factory.config.filter.watermark', WatermarkFactory::class);

    // Config services
    $services->set('liip_imagine.config.filter_factory_collection', FilterFactoryCollection::class)
        ->args([
            service('liip_imagine.factory.config.filter.auto_rotate'),
            service('liip_imagine.factory.config.filter.background'),
            service('liip_imagine.factory.config.filter.crop'),
            service('liip_imagine.factory.config.filter.downscale'),
            service('liip_imagine.factory.config.filter.flip'),
            service('liip_imagine.factory.config.filter.grayscale'),
            service('liip_imagine.factory.config.filter.interlace'),
            service('liip_imagine.factory.config.filter.paste'),
            service('liip_imagine.factory.config.filter.relative_resize'),
            service('liip_imagine.factory.config.filter.resize'),
            service('liip_imagine.factory.config.filter.rotate'),
            service('liip_imagine.factory.config.filter.scale'),
            service('liip_imagine.factory.config.filter.strip'),
            service('liip_imagine.factory.config.filter.thumbnail'),
            service('liip_imagine.factory.config.filter.upscale'),
            service('liip_imagine.factory.config.filter.watermark'),
        ]);

    $services->alias(FilterFactoryCollection::class, 'liip_imagine.config.filter_factory_collection');

    $services->set('liip_imagine.config.stack_builder', StackBuilder::class)
        ->args([
            service('liip_imagine.factory.config.stack'),
            service('liip_imagine.config.filter_factory_collection'),
        ]);

    $services->alias(StackBuilder::class, 'liip_imagine.config.stack_builder');

    $services->set('liip_imagine.config.stack_collection', StackCollection::class)
        ->public()
        ->args([
            service('liip_imagine.config.stack_builder'),
            '%liip_imagine.filter_sets%',
        ]);

    $services->alias(StackCollection::class, 'liip_imagine.config.stack_collection');

    // Utility services
    $services->set('liip_imagine.filter.manager', FilterManager::class)
        ->public()
        ->args([
            service('liip_imagine.filter.configuration'),
            service('liip_imagine'),
            service('liip_imagine.binary.mime_type_guesser'),
        ]);

    $services->alias(FilterManager::class, 'liip_imagine.filter.manager');

    $services->set('liip_imagine.data.manager', DataManager::class)
        ->public()
        ->args([
            service('liip_imagine.binary.mime_type_guesser'),
            service('liip_imagine.extension_guesser'),
            service('liip_imagine.filter.configuration'),
            '%liip_imagine.binary.loader.default%',
            '%liip_imagine.default_image%',
        ]);

    $services->alias(DataManager::class, 'liip_imagine.data.manager');

    $services->set('liip_imagine.cache.manager', CacheManager::class)
        ->public()
        ->args([
            service('liip_imagine.filter.configuration'),
            service('router'),
            service('liip_imagine.cache.signer'),
            service('event_dispatcher'),
            '%liip_imagine.cache.resolver.default%',
            '%liip_imagine.webp.generate%',
        ]);

    $services->alias(CacheManager::class, 'liip_imagine.cache.manager');

    $services->set('liip_imagine.filter.configuration', FilterConfiguration::class)
        ->args(['%liip_imagine.filter_sets%']);

    $services->set('liip_imagine.service.filter', FilterService::class)
        ->args([
            service('liip_imagine.data.manager'),
            service('liip_imagine.filter.manager'),
            service('liip_imagine.cache.manager'),
            '%liip_imagine.webp.generate%',
            '%liip_imagine.webp.options%',
            service('logger')->ignoreOnInvalid(),
        ]);

    $services->alias(FilterService::class, 'liip_imagine.service.filter');

    // Config
    $services->set('liip_imagine.controller.config', ControllerConfig::class)
        ->private()
        ->args(['']);

    // Controller
    $services->set(ImagineController::class)
        ->public()
        ->args([
            service('liip_imagine.service.filter'),
            service('liip_imagine.data.manager'),
            service('liip_imagine.cache.signer'),
            service('liip_imagine.controller.config'),
        ]);

    $services->alias('liip_imagine.controller', ImagineController::class)
        ->public();

    $services->set('liip_imagine.meta_data.reader', ExifMetadataReader::class)
        ->private();

    // ImagineInterface instances
    $services->alias('liip_imagine', 'liip_imagine.gd');

    $services->alias(ImagineInterface::class, 'liip_imagine');

    $services->set('liip_imagine.gd', Imagine::class)
        ->private()
        ->call('setMetadataReader', [service('liip_imagine.meta_data.reader')]);

    $services->set('liip_imagine.imagick', \Imagine\Imagick\Imagine::class)
        ->private()
        ->call('setMetadataReader', [service('liip_imagine.meta_data.reader')]);

    $services->set('liip_imagine.gmagick', \Imagine\Gmagick\Imagine::class)
        ->private()
        ->call('setMetadataReader', [service('liip_imagine.meta_data.reader')]);

    // Filter loaders
    $services->set('liip_imagine.filter.loader.relative_resize', RelativeResizeFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'relative_resize']);

    $services->set('liip_imagine.filter.loader.resize', ResizeFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'resize']);

    $services->set('liip_imagine.filter.loader.thumbnail', ThumbnailFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'thumbnail']);

    $services->set('liip_imagine.filter.loader.crop', CropFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'crop']);

    $services->set('liip_imagine.filter.loader.grayscale', GrayscaleFilterLoader::class)
        ->public()
        ->tag('liip_imagine.filter.loader', ['loader' => 'grayscale']);

    $services->set('liip_imagine.filter.loader.paste_image', PasteFilterLoader::class)
        ->args([
            service('liip_imagine'),
            '%kernel.project_dir%',
        ])
        ->tag('liip_imagine.filter.loader', ['loader' => 'paste_image']);

    // not officially deprecated because still injected and appears "used"
    $services->set('liip_imagine.filter.loader.paste', PasteFilterLoader::class)
        ->args([
            service('liip_imagine'),
            '%kernel.root_dir%',
        ])
        ->tag('liip_imagine.filter.loader', ['loader' => 'paste']);

    // not officially deprecated because still injected and appears "used"
    $services->set('liip_imagine.filter.loader.watermark', WatermarkFilterLoader::class)
        ->args([
            service('liip_imagine'),
            '%kernel.root_dir%',
        ])
        ->tag('liip_imagine.filter.loader', ['loader' => 'watermark']);

    $services->set('liip_imagine.filter.loader.watermark_image', WatermarkFilterLoader::class)
        ->args([
            service('liip_imagine'),
            '%kernel.project_dir%',
        ])
        ->tag('liip_imagine.filter.loader', ['loader' => 'watermark_image']);

    $services->set('liip_imagine.filter.loader.background', BackgroundFilterLoader::class)
        ->args([service('liip_imagine')])
        ->tag('liip_imagine.filter.loader', ['loader' => 'background']);

    $services->set('liip_imagine.filter.loader.strip', StripFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'strip']);

    $services->set('liip_imagine.filter.loader.scale', ScaleFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'scale']);

    $services->set('liip_imagine.filter.loader.upscale', UpscaleFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'upscale']);

    $services->set('liip_imagine.filter.loader.downscale', DownscaleFilterLoader::class)
        ->public()
        ->tag('liip_imagine.filter.loader', ['loader' => 'downscale']);

    $services->set('liip_imagine.filter.loader.auto_rotate', AutoRotateFilterLoader::class)
        ->tag('liip_imagine.filter.loader', ['loader' => 'auto_rotate']);

    $services->set('liip_imagine.filter.loader.rotate', RotateFilterLoader::class)
        ->public()
        ->tag('liip_imagine.filter.loader', ['loader' => 'rotate']);

    $services->set('liip_imagine.filter.loader.flip', FlipFilterLoader::class)
        ->public()
        ->tag('liip_imagine.filter.loader', ['loader' => 'flip']);

    $services->set('liip_imagine.filter.loader.interlace', InterlaceFilterLoader::class)
        ->public()
        ->tag('liip_imagine.filter.loader', ['loader' => 'interlace']);

    $services->set('liip_imagine.filter.loader.resample', ResampleFilterLoader::class)
        ->public()
        ->args([service('liip_imagine')])
        ->tag('liip_imagine.filter.loader', ['loader' => 'resample']);

    $services->set('liip_imagine.filter.loader.fixed', FixedFilterLoader::class)
        ->public()
        ->tag('liip_imagine.filter.loader', ['loader' => 'fixed']);

    // Data loaders
    $services->set('liip_imagine.binary.loader.prototype.filesystem', FileSystemLoader::class)
        ->args([
            service('liip_imagine.mime_type_guesser'),
            service('liip_imagine.extension_guesser'),
            '', // will be injected by FileSystemLoaderFactory
        ]);

    $services->set('liip_imagine.binary.loader.prototype.stream', StreamLoader::class)
        ->args([
            '', // will be injected by StreamLoaderFactory
            '', // will be injected by StreamLoaderFactory
        ]);

    $services->set('liip_imagine.binary.loader.prototype.flysystem', FlysystemLoader::class)
        ->abstract()
        ->args([
            service('liip_imagine.extension_guesser'),
            '', // will be injected by FlysystemLoaderFactory
        ]);

    $services->set('liip_imagine.binary.loader.prototype.flysystem2', FlysystemV2Loader::class)
        ->abstract()
        ->args([
            service('liip_imagine.extension_guesser'),
            '', // will be injected by FlysystemV2LoaderFactory
        ]);

    $services->set('liip_imagine.binary.loader.prototype.chain', ChainLoader::class)
        ->abstract()
        ->args(['']); // will be injected by ChainLoaderFactory

    // Data loader locators
    $services->set('liip_imagine.binary.locator.filesystem', FileSystemLocator::class)
        ->share(false)
        ->private()
        ->args([
            '', // will be injected by FilesystemLoaderFactory
            '', // will be injected by FilesystemLoaderFactory
        ])
        ->tag('liip_imagine.binary.locator', ['shared' => false]);
    $services->set('liip_imagine.binary.locator.asset_mapper', AssetMapperLocator::class)
        ->abstract()
        ->private()
        ->args([
            '', // will be injected by AssetMapperLoaderFactory
        ])
        ->tag('liip_imagine.binary.locator', ['shared' => true]);

    $services->set('liip_imagine.binary.locator.filesystem_insecure', FileSystemInsecureLocator::class)
        ->share(false)
        ->private()
        ->args([
            '', // will be injected by FilesystemLoaderFactory
            '', // will be injected by FilesystemLoaderFactory
        ])
        ->tag('liip_imagine.binary.locator', ['shared' => false]);

    // Cache resolver
    $services->set('liip_imagine.cache.resolver.prototype.web_path', WebPathResolver::class)
        ->public()
        ->abstract()
        ->args([
            service('filesystem'),
            service('router.request_context'),
            '', // will be injected by WebPathResolverFactory
            '', // will be injected by WebPathResolverFactory
        ]);

    $services->set('liip_imagine.cache.resolver.prototype.aws_s3', AwsS3Resolver::class)
        ->public()
        ->abstract()
        ->args([
            '', // will be injected by AwsS3ResolverFactory
            '', // will be injected by AwsS3ResolverFactory
            '', // will be injected by AwsS3ResolverFactory
            '', // will be injected by AwsS3ResolverFactory
            '', // will be injected by AwsS3ResolverFactory
        ]);

    $services->set('liip_imagine.cache.resolver.prototype.cache', CacheResolver::class)
        ->public()
        ->abstract()
        ->args([
            // args will be injected by a ResolverFactory
            '',
            '',
        ]);

    $services->set('liip_imagine.cache.resolver.prototype.flysystem', FlysystemResolver::class)
        ->public()
        ->abstract()
        ->args([
            '', // will be injected by a ResolverFactory
            service('router.request_context'),
            '', // will be injected by a ResolverFactory
            '', // will be injected by a ResolverFactory
            '', // will be injected by a ResolverFactory
        ]);

    $services->set('liip_imagine.cache.resolver.prototype.flysystem2', FlysystemV2Resolver::class)
        ->public()
        ->abstract()
        ->args([
            '',
            service('router.request_context'),
            '',
            '',
            '',
        ]);

    $services->set('liip_imagine.cache.resolver.prototype.proxy', ProxyResolver::class)
        ->public()
        ->abstract()
        ->args([
            '', // will be injected by AwsS3ResolverFactory
            '', // will be injected by AwsS3ResolverFactory
        ]);

    $services->set('liip_imagine.cache.resolver.prototype.psr_cache', PsrCacheResolver::class)
        ->public()
        ->abstract()
        ->args([
            '', // will be injected by a ResolverFactory
            '', // will be injected by a ResolverFactory
        ]);

    $services->set('liip_imagine.cache.resolver.no_cache_web_path', NoCacheWebPathResolver::class)
        ->public()
        ->args([service('router.request_context')])
        ->tag('liip_imagine.cache.resolver', ['resolver' => 'no_cache']);

    // Form types
    $services->set('liip_imagine.form.type.image', ImageType::class)
        ->tag('form.type', ['alias' => 'liip_imagine_image']);

    // Guessers
    $services->set('liip_imagine.mime_type_guesser', 'Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface')
        ->factory(['Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser', 'getInstance']);

    $services->set('liip_imagine.extension_guesser', 'Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface')
        ->factory(['Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser', 'getInstance']);

    $services->set('liip_imagine.binary.mime_type_guesser', SimpleMimeTypeGuesser::class)
        ->args([service('liip_imagine.mime_type_guesser')]);

    $services->set('liip_imagine.cache.signer', Signer::class)
        ->public()
        ->args(['%kernel.secret%']);

    // Post processors
    $services->set('liip_imagine.filter.post_processor.jpegoptim', JpegOptimPostProcessor::class)
        ->args([
            '%liip_imagine.jpegoptim.binary%',
            '%liip_imagine.jpegoptim.stripAll%',
            '%liip_imagine.jpegoptim.max%',
            '%liip_imagine.jpegoptim.progressive%',
            '%liip_imagine.jpegoptim.tempDir%',
        ])
        ->tag('liip_imagine.filter.post_processor', ['post_processor' => 'jpegoptim']);

    $services->set('liip_imagine.filter.post_processor.optipng', OptiPngPostProcessor::class)
        ->args([
            '%liip_imagine.optipng.binary%',
            '%liip_imagine.optipng.level%',
            '%liip_imagine.optipng.stripAll%',
            '%liip_imagine.optipng.tempDir%',
        ])
        ->tag('liip_imagine.filter.post_processor', ['post_processor' => 'optipng']);

    $services->set('liip_imagine.filter.post_processor.pngquant', PngquantPostProcessor::class)
        ->args(['%liip_imagine.pngquant.binary%'])
        ->tag('liip_imagine.filter.post_processor', ['post_processor' => 'pngquant']);

    $services->set('liip_imagine.filter.post_processor.mozjpeg', MozJpegPostProcessor::class)
        ->args(['%liip_imagine.mozjpeg.binary%'])
        ->tag('liip_imagine.filter.post_processor', ['post_processor' => 'mozjpeg']);

    $services->set('liip_imagine.filter.post_processor.cwebp', CwebpPostProcessor::class)
        ->args([
            '%liip_imagine.cwebp.binary%',
            '%liip_imagine.cwebp.tempDir%',
            '%liip_imagine.cwebp.q%',
            '%liip_imagine.cwebp.alphaQ%',
            '%liip_imagine.cwebp.m%',
            '%liip_imagine.cwebp.alphaFilter%',
            '%liip_imagine.cwebp.alphaMethod%',
            '%liip_imagine.cwebp.exact%',
            '%liip_imagine.cwebp.metadata%',
        ])
        ->tag('liip_imagine.filter.post_processor', ['post_processor' => 'cwebp']);
};
