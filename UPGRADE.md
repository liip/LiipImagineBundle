
# Upgrade Notices

This file contains a descriptive enumeration of *important* changes that may require *manual intervention* in your
application code or are otherwise particularly noteworthy. Reference our full
[changelog](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md) for a complete list of all changes for a
given release.

## [Unreleased](https://github.com/liip/LiipImagineBundle/tree/HEAD)

- The `watermark` and `paste` filters were deprecated and will immediately
  *not* work in Symfony 5.0 or higher. Use `watermark_image` and `paste_image`
  instead. The corresponding services - `liip_imagine.filter.loader.paste`
  and `liip_imagine.filter.loader.image` are *also* deprecated, but not marked
  as such. These are both unavailable in Symfony 5.0.
- __\[Composer\]__ Allow [league/flysystem](https://github.com/thephpleague/flysystem) version 2.0.
  You can use `league/flysystem` either v1 or v2, but if you're using v1
  and want to upgrade to v2 you can simply run `composer require -W liip/imagine-bundle:^2.5 league/flysystem:^2.0`.
  You should upgrade flysystem v2 related packages that you're using as well.

## [2.2.0](https://github.com/liip/LiipImagineBundle/blob/2.2.0/CHANGELOG.md#unreleased)

*Released on* 2019-04-10 *and assigned* [`2.2.0`](https://github.com/liip/LiipImagineBundle/releases/tag/2.2.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/2.0.0...2.2.0)\).*
- __[Deprecated]__ Constructing `FileSystemLoader`, `FlysystemLoader`, `SimpleMimeTypeGuesser` and `DataManager` with 
`\Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface` and 
`\Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface` have been deprecated for Symfony 4.3+ in 
favor of the [new interfaces](https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.3.md#httpfoundation).
- __[Utility]__ __[BC BREAK]__ The `SymfonyFramework` class marked as `internal` has been declared as final.

## [2.0.0](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md#191)

*Released on* 2018-04-06 *and assigned* [`2.0.0`](https://github.com/liip/LiipImagineBundle/releases/tag/2.0.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.9.1...2.0.0)\).*


  - __[Post Processor]__  __\[BC BREAK\]__ The `PostProcessorConfigurablePostProcessorInterface` interface has been completely removed
  and the `PostProcessorInterface` interface has been updated to allow passing the configuration array to its
  `process` method as the second parameter. The `PostProcessorInterface::process()` now implements the following
  signature: `process(BinaryInterface $binary, array $options = []): BinaryInterface`. All custom post processors
  in your project must be updated to match this new signature.

  - __[Dependencies]__ The `imagine/Imagine` dependency has been updated from the `0.6.x` series to require `0.7.1` or
  greater. If you project has a hard dependency on any prior version, you will need to update your dependencies.

  - __[Form]__ The legacy `setDefaultOptions()` and `getName()` methods on `Form/Type/ImageType` have been removed, as
  these methods are no longer required for Symfony. If using them, you will need to update your implementation.

  - __[Dependency Injection]__ The `DependencyInjection/Factory/ChildDefinitionTrait` trait has been removed, as it
  handled logic to support legacy Symfony versions no longer targeted.

  - __[Dependency Injection]__ The compiler pass `log` method signature has changed to
  `log(ContainerBuilder $container, string $message, ...$replacements): void`. If you are extending
  `AbstractCompilerPass` and using this protected method, you must update your usage.

  - __[Dependency Injection]__ The default values for the `liip_imagine.loaders.<name>.filesystem.data_root` and
  `liip_imagine.resolvers.<name>.web_path.web_root` configuration options are now variable based on the Symfony version.
  For Symfony `4.0.0` and later, the value is `%kernel.project_dir%/public`, and for prior releases (such as the Symfony
  `3.x`), the value is `%kernel.project_dir%/web`. This should automatically provide a suitable default for the
  different directory structures of the `4.x` and `3.x` Symfony releases.

  - __[Dependency Injection]__ __[Filter]__ A new filter service abstraction is available as
  `liip_imagine.service.filter` with a `createFilteredBinary($path, $filter, array $runtimeFilters = [])` method to
  quickly get the filtered binary image and a `getUrlOfFilteredImage($path, $filter, $resolver = null)` method to
  quickly resolve and get the filtered image URL.

  - __[Data Loader]__ The `FileSystemLoader::__construct()` method signature has changed in accordance with the prior
  deprecation notice; the third parameter must be of signature
  `\Liip\ImagineBundle\Binary\Locator\LocatorInterface $locator` and the fourth parameter must be of signature
  `array $dataRoots`.

  - __[Data Loader]__ The `GridFSLoader` data loader has been removed as the required
  [mongo](https://pecl.php.net/package/mongo) extension has been deprecated and will not be ported to PHP `7.x`.

  - __[Dependency Injection]__ A new interface `\Liip\ImagineBundle\DependencyInjection/Factory/FactoryInterface` has
  been introduced and is shared between the loaders (`LoaderFactoryInterface`) and resolvers
  (`ResolverFactoryInterface`).

  - __[Dependency Injection]__ All class name parameters have been removed from the service definitions. Instead of
  overwriting the class name parameters to provide your own implementation, use
  [service decoration](http://symfony.com/doc/current/service_container/service_decoration.html).

  - __[Data Transformer]__ The data transformer interface
  (`\Liip\ImagineBundle\Imagine\Data\Transforme\TransformerInterface`) was deprecated in version `1.x` and has been
  removed.

  - __[Templating]__ The imagine extension `\Liip\ImagineBundle\Templating\ImagineExtension` has been renamed to
  `FilterExtension`. Similarly, the template helper `\Liip\ImagineBundle\Templating\Helper\ImagineHelper` has been
  renamed to `FilterHelper`.

  - __[Utility]__ The `\Liip\ImagineBundle\Utility/Framework/SymfonyFramework::hasDefinitionSharing()` method has been
  removed due to our higher Symfony requirements rending it unnecessary.

  - __[General]__ The use of fully-qualified class name strings is no longer supported and the `::class` compile-time
  class constant is now used.
    
  - __[Enqueue]__ Enqueue's producer send() method has been deprecated and will be removed, use sendCommand() instead.
  When interacting with the producer to resolve images in the background you must make the following changes to your
  code:

    ```php
    <?php

      // 1.0
      $producer->send(\Liip\ImagineBundle\Async\Topics::RESOLVE_CACHE /* ... */);

      // 2.0
      $producer->sendCommand(\Liip\ImagineBundle\Async\Commands::RESOLVE_CACHE /* ... */);
    ```

  - __[Routing]__ The `Resources/config/routing.xml` file has been removed. Use the new `Resources/config/routing.yaml` YAML file instead.


## [1.9.1](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md#191)

*Released on* 2017-09-08 *and assigned* [`1.9.1`](https://github.com/liip/LiipImagineBundle/releases/tag/1.9.1) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.9.0...1.9.1)\).*

- __\[Console\]__ __\[BC BREAK\]__ The resolve command's --as-script/-s option/shortcut renamed to --machine-readable/-m \(fixes [\#988](https://github.com/liip/LiipImagineBundle/pull/988)\), its output updated to aligned with the resolve command, and the "--machine-readable/-m" option added.  [\#991](https://github.com/liip/LiipImagineBundle/pull/991) *([robfrawley](https://github.com/robfrawley))*


## [1.9.0](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md#190)

*Released on* 2017-08-30 *and assigned* [`1.9.0`](https://github.com/liip/LiipImagineBundle/releases/tag/1.9.0) *tag \([view verbose changelog](https://github.com/liip/LiipImagineBundle/compare/1.8.0...1.9.0)\).*

- __\[Tests\]__ Fix filesystem loader deprecation message in tests. [\#982](https://github.com/liip/LiipImagineBundle/pull/982) *([robfrawley](https://github.com/robfrawley))*
- __\[Filter\]__ Add "centerright" and "centerleft" positions to background filter. [\#974](https://github.com/liip/LiipImagineBundle/pull/974) *([cmodijk](https://github.com/cmodijk))*
- __\[Config\]__ Allow to configure the HTTP response code for redirects. [\#970](https://github.com/liip/LiipImagineBundle/pull/970) *([lstrojny](https://github.com/lstrojny))*
- __\[Console\]__ Added --force option, renamed --filters to --filter, and made resolve command output pretty. [\#967](https://github.com/liip/LiipImagineBundle/pull/967) *([robfrawley](https://github.com/robfrawley))*
- __\[CS\]__ Fix two docblock annotations. [\#965](https://github.com/liip/LiipImagineBundle/pull/965) *([imanalopher](https://github.com/imanalopher))*
- __\[Data Loader\]__ __\[Deprecation\]__ The FileSystemLoader no longer accepts an array of data root paths; instead pass a FileSystemLocator, which should instead be passed said paths. [\#963](https://github.com/liip/LiipImagineBundle/pull/963/) *([robfrawley](https://github.com/robfrawley), [rpkamp](https://github.com/rpkamp))*
- __\[Composer\]__ Allow [avalanche123/Imagine](https://github.com/avalanche123/Imagine) version 0.7.0. [\#958](https://github.com/liip/LiipImagineBundle/pull/958) *([robfrawley](https://github.com/robfrawley))*
- __\[Data Loader\]__ __\[Documentation\]__ Add chain loader documentation. [\#957](https://github.com/liip/LiipImagineBundle/pull/957) *([robfrawley](https://github.com/robfrawley))*
- __\[Data Loader\]__ Add chain loader implementation. [\#953](https://github.com/liip/LiipImagineBundle/pull/953) *([robfrawley](https://github.com/robfrawley))*
- __\[CS\]__ Fix templating extension method return type. [\#951](https://github.com/liip/LiipImagineBundle/pull/951) *([imanalopher](https://github.com/imanalopher))*
- __\[Dependency Injection\]__ Fix compiler pass log message typo. [\#947](https://github.com/liip/LiipImagineBundle/pull/947) *([you-ser](https://github.com/you-ser))*
- __\[Travis\]__ Default to trusty container image \(with precise image for php 5.3\). [\#945](https://github.com/liip/LiipImagineBundle/pull/945) *([robfrawley](https://github.com/robfrawley))*
- __\[Enqueue\]__ Use simplified transport configuration. [\#942](https://github.com/liip/LiipImagineBundle/pull/942) *([makasim](https://github.com/makasim))*
- __\[Filter\]__ Add resolution loader implementation. [\#941](https://github.com/liip/LiipImagineBundle/pull/941) *([robfrawley](https://github.com/robfrawley))*
- __\[Travis\]__ Remove Symfony 3.3 from allowed failures. [\#940](https://github.com/liip/LiipImagineBundle/pull/940) *([robfrawley](https://github.com/robfrawley))*
- __\[Utility\]__ Use simplified Symfony kernel version comparison operation. [\#939](https://github.com/liip/LiipImagineBundle/pull/939) *([robfrawley](https://github.com/robfrawley))*


## [1.8.0](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md#180)

 - __[Routing]__ The `Resources/config/routing.xml` file has been deprecated and will be removed in `2.0`. Use the new
 YAML variant moving forward `Resources/config/routing.yaml`.


## [1.7.3](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md#173)

  - __[Data Loader]__ The `FileSystemLoader` now allows you to assign keys to data roots, and directly reference them
  when requesting resources.

    ```yml

      # provide index for data roots
      liip_imagine:
        loaders:
          default:
            filesystem:
              data_root:
                foo: /path/to/foo
                bar: /path/to/bar

    ```

    Assume you have a file name `file.ext` in both data root paths. Given the above configuration, you can specifically
    request the file from the `/path/to/foo` root using the following file syntax: `@foo:file.ext`. Similarly, you can
    request the same file from `/path/to/bar` using `@bar:file.ext`. Note, that the auto-registered bundles (detailed
    below) are given indexes of their short bundle name (for example, given the bundle `FooBundle`, you can request a
    file from its public resources path via `@FooBundle:path/to/file.ext`).

  - __[Data Loader]__ The `FileSystemLoader` now supports automatically registering the `Resources/public` folders
  within all loaded bundles. This can be enabled via the following configuration.

    ```yml

      # enable bundle auto-registration
      liip_imagine:
        loaders:
          default:
            filesystem:
              bundle_resources:
                enabled: true

    ```

    Additionally, you can whitelist or blacklist specific bundles from the auto-registration routine.

    ```yml

      # blacklist "FooBundle" from auto-registration
      liip_imagine:
        loaders:
          default:
            filesystem:
              bundle_resources:
                enabled: true
                access_control_type: blacklist
                access_control_list
                  - FooBundle

      # whitelist "BarBundle" from auto-registration
      liip_imagine:
        loaders:
          default:
            filesystem:
              bundle_resources:
                enabled: true
                access_control_type: whitelist
                access_control_list
                  - BarBundle

    ```

  - __[Data Locator]__ The `*Locator` services passed to `FileSystemLoader` are now marked as "non-shared" or
  "prototype" within the DI container, resulting in new instances being passed every time the services are requested.


## [1.7.2](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md#172)

  - __[Data Loader]__ The `FileSystemLoader`'s resource locator has been abstracted out into `FileSystemLocator`
  (provides the same `realpath`-based locator algorithm introduced in the `1.7.0` release) and
  `FileSystemInsecureLocator` (provides the old locator algorithm from version `1.6.x` and prior).

    The latter implementation can present security concerns, as it will blindly following symbolic links, including
    those that point outside your configured `data_root` directory(ies). It is *not recommended* unless your deployment
    process relies heavily on multi-level symbolic links that renders the new locator difficult (and sometime
    impossible) to setup.

  - __[Deprecation]__ __[Data Loader]__ Instantiating `FileSystemLoader` without providing a forth constructor argument
  of signature `\Liip\ImagineBundle\Binary\Locator\LocatorInterface $locator` is deprecated and the ability to do so
  will be removed in the next major release, `2.0`.

  - __[Configuration]__ The `liip_imagine.loaders.default.filesystem.locator` bundle configuration option has been
  introduced and allows the following `enum` values: `filesystem` and `filesystem_insecure`. These correspond to the
  aforementioned `FileSystemLocator` and `FileSystemInsecureLocator` resource locator implementations that affect the
  behavior of `FileSystemLoader`. This option defaults to `filesystem`.

    ```yml

      # use the current, default locator algorithm
      liip_imagine:
        loaders:
          default:
            filesystem:
              locator: filesystem

      # use the old (pre 0.7.x) locator algorithm
      liip_imagine:
        loaders:
          default:
            filesystem:
              locator: filesystem_insecure

    ```

  - __[Dependency Injection]__ All compiler passes (filters, post-processors, data loaders, cache resolvers, etc) have
  been updated to log their behavior, allowing you to easily debug tagged services, including both core-provided and
  custom services defined by your application). In Symfony `>= 3.2` this output is located in the 
  `var/cache/[dev|prod|env]/app*ProjectContainerCompiler.log` file. Output will be similar to the following example on
  a fresh install.

    ```

      LoadersCompilerPass: Registered imagine-bimdle binary loader: liip_imagine.binary.loader.default
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.relative_resize
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.resize
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.thumbnail
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.crop
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.grayscale
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.paste
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.watermark
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.background
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.strip
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.scale
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.upscale
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.downscale
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.auto_rotate
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.rotate
      FiltersCompilerPass: Registered imagine-bimdle filter loader: liip_imagine.filter.loader.interlace
      PostProcessorsCompilerPass: Registered imagine-bimdle filter post-processor: liip_imagine.filter.post_processor.jpegoptim
      PostProcessorsCompilerPass: Registered imagine-bimdle filter post-processor: liip_imagine.filter.post_processor.optipng
      PostProcessorsCompilerPass: Registered imagine-bimdle filter post-processor: liip_imagine.filter.post_processor.pngquant
      PostProcessorsCompilerPass: Registered imagine-bimdle filter post-processor: liip_imagine.filter.post_processor.mozjpeg
      ResolversCompilerPass: Registered imagine-bimdle cache resolver: liip_imagine.cache.resolver.default
      ResolversCompilerPass: Registered imagine-bimdle cache resolver: liip_imagine.cache.resolver.no_cache_web_path

    ```


## [1.7.1](https://github.com/liip/LiipImagineBundle/blob/2.0/CHANGELOG.md#171)

  - __[Data Loader]__ The `FileSystemLoader` data loader performs a more robust security check against image resource
    paths to ensure they reside within the defined data root path(s). If utilizing symbolic links, you should reference
    the troubleshooting guide at the end of this upgrade notice.

  - __[Data Loader]__ The `FileSystemLoader` data loader now accepts an array of paths (as strings) for its third
    constructor argument, enabling the loader to check multiple paths for the requested image resource.
    *Note: this change breaks those extending this class and relying on the protected property `$dataRoot`, which has
    been renamed to `$dataRoots` and is now of the type `string[]` instead of the prior type of `string`.*

  - __[Configuration]__ The `liip_imagine.loaders.default.filesystem.data_root` bundle configuration option now accepts
    an array of paths (as strings) or a single scalar path ()if only one is required for your configuration), allowing
    the `filesystem` data loader to check multiple data root paths for the requested image resource. The following YML
    configuration shows examples for all allowed value types.

    ```yml

      # provide an array of scalar paths
      liip_imagine:
        loaders:
          default:
            filesystem:
              data_root:
                - /multiple/root/paths/foo
                - /multiple/root/paths/bar

      # provide an single scalar path
      liip_imagine:
        loaders:
          default:
            filesystem:
              data_root: /single/root/path

    ```
  
  - __[Troubleshooting]__ If you are using the `filesystem` data loader and have *symbolic links* within the `data_root`
    that point outside this path (the `data_root` option defaults to `%kernel.root_dir%/../web`) then you are *required*
    to configure all outside resource paths as additional `data_root` paths using the following option key in your
    application's configuration: `liip_imagine.loaders.default.filesystem.data_root`.
    
    The following is a list of the most common exception error messages encountered when the `data_root` option is not
    correctly configured:

    - > Source image not resolvable "%s" in root path(s) "%s"
    - > Source image invalid "%s" as it is outside of the defined root path(s) "%s"


## 1.0.0-alpha7

  - __[Configuration]__ `liip_imagine.controller_action` option was removed in favour of an array of actions. See 
    `liip_imagine.controller` config

      ```diff

        -liip_imagine:
        -    controller_action: AcmeDemoBundle:Default:filterAction
        +liip_imagine:
        +    controller:
        +       filter_action: AcmeDemoBundle:Default:filterAction

      ```


## 1.0.0-alpha6

  - __[Route]__ `ImagineLoader` was removed. Please adjust your `app/config/routing.yml` file.

      ```diff
      
        -_imagine:
        -    resource: .
        -    type:     imagine
        +_liip_imagine:
        +    resource: "@LiipImagineBundle/Resources/config/routing.xml"
      
      ```

  - __[Configuration]__ `liip_imagine.filter_sets.route` option and sub options were removed.

  - __[Configuration]__ `liip_imagine.cache_prefix` option was removed.


## 1.0.0-alpha5

  - __[Symfony]__ Required minimum symfony version was updated to 2.3.

  - __[Logger]__ Symfony `LoggerInterface` was replaced with PSR-3 one.

  - __[Cache]__ New `isStored` method was added.

  - __[Cache]__ The method `ResolverInterface::getBrowserPath` was removed.

  - __[Cache]__ The method `ResolverInterface::store` accept `BinaryInterface` as first argument.

  - __[Cache]__ The method `ResolverInterface::store` return nothing.

  - __[Cache]__ The method `ResolverInterface::remove` return nothing.

  - __[Cache]__ The method `ResolverInterface::remove` takes required array of filter as first argument.

  - __[Cache]__ The method `ResolverInterface::remove` takes optional path as second argument.

  - __[Cache]__ The method `ResolverInterface::clean` was removed.

  - __[Cache]__ The method `ResolverInterface::resolve` takes path and filter as arguments.

  - __[Cache]__ The method `ResolverInterface::resolve` return absolute url of the cached image.

  - __[Cache]__ The method `CacheManager::resolve` may throw OutOfBoundsException if required resolver does not exist.

  - __[Cache]__ The method `CacheManager::resolve` return absolute url of the cached image.

  - __[Cache]__ The method `CacheManager::store` accept `BinaryInterface` as first argument.

  - __[Cache]__ The method `CacheManager::store` return nothing.

  - __[Cache]__ The method `CacheManager::clearResolversCache` was removed.

  - __[Cache]__ The method `CacheManager::getWebRoot` was removed.

  - __[Cache]__ The method `CacheManager::getBrowserPath` third argument was changed, now it is `runtimeConfig`.

  - __[Cache]__ The method `CacheManager::generateUrl` third argument was changed, now it is `runtimeConfig`.

  - __[Cache]__ `NoCacheResolver` renamed to `NoCacheWebPathResolver`.

  - __[Cache]__ `AbstractFilesystemResolver` was removed.

  - __[Data Loader]__ `LoaderInterface::find` now can return string or `BinaryInterface` instance.

  - __[Data Loader]__ `DataManager::find` now can return `BinaryInterface` instance only.

  - __[Data Loader]__ All data loaders moved to `Binary/Loader` folder.

  - __[Data Loader]__ Tag name `liip_imagine.data.loader` changed to `liip_imagine.binary.loader`

  - __[Data Loader]__ Parameter key `liip_imagine.data.loader.filesystem.class` changed to 
    `liip_imagine.binary.loader.filesystem.class`

  - __[Data Loader]__ Parameter key `liip_imagine.data.loader.stream.class` changed to 
    `liip_imagine.binary.loader.stream.class`

  - __[Data Loader]__ Service id `liip_imagine.data.loader.prototype.filesystem` changed to 
    `liip_imagine.binary.loader.prototype.filesystem`

  - __[Data Loader]__ Service id `liip_imagine.data.loader.prototype.stream` changed to 
    `liip_imagine.binary.loader.prototype.stream`

  - __[Filter]__ `FilterManager::applyFilter` now return instance of `BinaryInterface`.

  - __[Filter]__ `FilterManager::applyFilter` first argument was changed from Image instance to BinaryInterface one.

  - __[Filter]__ `FilterManager::get` was removed.

  - __[Configuration]__ `liip_imagine.filter_sets.path` option was removed.

  - __[Configuration]__ `liip_imagine.filter_sets.format` option was removed.

  - __[Configuration]__ `liip_imagine.cache_mkdir_mode` option was removed.

  - __[Configuration]__ `liip_imagine.web_root` option was removed.

  - __[Configuration]__ `liip_imagine.cache` default value was changed from `web_path`to `default`.

  - __[Configuration]__ `liip_imagine.formats` option was removed.

  - __[Configuration]__ `liip_imagine.data_root` option was removed.
