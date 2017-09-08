
# Upgrade

All important upgrade requirements will be enumerated in this
[`UPGRADE.md`](https://github.com/liip/LiipImagineBundle/blob/1.0/UPGRADE.md) file and all notable alterations to this project will be documented in the
[`CHANGELOG.md`](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md) file.
This project adheres to [semantic versioning](http://semver.org/spec/v2.0.0.html).


## v1.9.1

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v191) for the `1.9.1` release.*

 - __\[Console\]__ __\[BC BREAK\]__ The resolve command's `--as-script`/`-s` option name/shortcut conflicted with Symfony
 2.x core console options (specifically `--shell`/`-s`) and has been renamed to `--machine-readable`/`-m`
 \(fixes [\#988](https://github.com/liip/LiipImagineBundle/pull/988)\). The `-s` option shortcut was the only conflict,
 but the `--as-script` option name proved confusing and unclear so it too was renamed.

 - __\[Console\]__ The output formatting for the `remove` command has been updated and aligned with the behavior
 previously introduced in `1.9.0` for the `resolve` command, making both of them consistent and in-line with the
 expected `2.0.0` output. The `--machine-readable`/`-m` option name/shortcut has now been added to the `remove` command
 as well, enabling predictable, consistent, script parseable output stripped of text styles and supplemental formatting.


## v1.9.0

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v190) for the `1.9.0` release.*

 - __\[Data Loader\]__ The arguments for the `FileSystemLoader` class constructor have changed. Passing an array of roots
 as the third parameter and an (optional) `LocatorInterace` as the fourth parameter is deprecated. A `LocatorInterface`
 should now be passed as third parameter, and the array of data roots to the `LocatorInterface::__construct()` method
 directly. All prior signatures will continue to work until `2.0` is release.

 - __\[Console\]__ Added the `--force` parameter to resolve console command to force image resolution regardless of cache.
 Added the `--as-script` parameter to resolve console command to disable verbose, "prettified" output.

 - __\[Composer\]__ Imagine library upgraded to version 0.7.x.


## v1.8.0

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v180) for the `1.8.0` release.*

 - __\[Routing\]__ The `Resources/config/routing.xml` file has been deprecated and will be removed in `2.0`. Use the new
 YAML variant moving forward `Resources/config/routing.yaml`.


## v1.7.3

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v173) for the `1.7.3` release.*

  - __\[Data Loader\]__ The `FileSystemLoader` now allows you to assign keys to data roots, and directly reference them when
  requesting resources.

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
    request the same file from `/path/to/bar` using `@bar:file.ext`. Note, that the auto-registered bundles (detailed below)
    are given indexes of their short bundle name (for example, given the bundle `FooBundle`, you can request a file from
    its public resources path via `@FooBundle:path/to/file.ext`).

  - __\[Data Loader\]__ The `FileSystemLoader` now supports automatically registering the `Resources/public` folders within
  all loaded bundles. This can be enabled via the following configuration.

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

  - __\[Data Locator\]__ The `*Locator` services passed to `FileSystemLoader` are now marked as "non-shared" or "prototype"
  within the DI container, resulting in new instances being passed every time the services are requested.


## v1.7.2

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v172) for the `1.7.2` release.*

  - __\[Data Loader\]__ The `FileSystemLoader`'s resource locator has been abstracted out into `FileSystemLocator`
  (provides the same `realpath`-based locator algorithm introduced in the `1.7.0` release) and `FileSystemInsecureLocator`
  (provides the old locator algorithm from version `1.6.x` and prior).

    The latter implementation can present security concerns, as it will blindly following symbolic links, including those
  that point outside your configured `data_root` directory(ies). It is *not recommended* unless your deployment process
  relies heavily on multi-level symbolic links that renders the new locator difficult (and sometime impossible) to
setup.

  - __\[Deprecation\]__ __\[Data Loader\]__ Instantiating `FileSystemLoader` without providing a forth constructor argument
  of signature `\Liip\ImagineBundle\Binary\Locator\LocatorInterface $locator` is deprecated and the ability to do so
  will be removed in the next major release, `2.0`.

  - __\[Configuration\]__ The `liip_imagine.loaders.default.filesystem.locator` bundle configuration option has been
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

  - __\[Dependency Injection\]__ All compiler passes (filters, post-processors, data loaders, cache resolvers, etc) have
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


## v1.7.1

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v171) for the `1.7.1` release.*

  - __\[Data Loader\]__ The `FileSystemLoader` data loader performs a more robust security check against image resource
    paths to ensure they reside within the defined data root path(s). If utilizing symbolic links, you should reference
    the troubleshooting guide at the end of this upgrade notice.

  - __\[Data Loader\]__ The `FileSystemLoader` data loader now accepts an array of paths (as strings) for its third
    constructor argument, enabling the loader to check multiple paths for the requested image resource.
    *Note: this change breaks those extending this class and relying on the protected property `$dataRoot`, which has
    been renamed to `$dataRoots` and is now of the type `string[]` instead of the prior type of `string`.*

  - __\[Configuration\]__ The `liip_imagine.loaders.default.filesystem.data_root` bundle configuration option now accepts
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

  - __\[Troubleshooting\]__ If you are using the `filesystem` data loader and have *symbolic links* within the `data_root`
    that point outside this path (the `data_root` option defaults to `%kernel.root_dir%/../web`) then you are *required*
    to configure all outside resource paths as additional `data_root` paths using the following option key in your
    application's configuration: `liip_imagine.loaders.default.filesystem.data_root`.

    The following is a list of the most common exception error messages encountered when the `data_root` option is not
    correctly configured:

    - > Source image not resolvable "%s" in root path(s) "%s"
    - > Source image invalid "%s" as it is outside of the defined root path(s) "%s"


## v1.0.0-alpha7

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v100-alpha7) for the `1.0.0-alpha7` release.*

  - __\[Configuration\]__ `liip_imagine.controller_action` option was removed in favour of an array of actions. See
    `liip_imagine.controller` config

      ```diff

        -liip_imagine:
        -    controller_action: AcmeDemoBundle:Default:filterAction
        +liip_imagine:
        +    controller:
        +       filter_action: AcmeDemoBundle:Default:filterAction

      ```


## v1.0.0-alpha6

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v100-alpha6) for the `1.0.0-alpha6` release.*

  - __\[Route\]__ `ImagineLoader` was removed. Please adjust your `app/config/routing.yml` file.

      ```diff

        -_imagine:
        -    resource: .
        -    type:     imagine
        +_liip_imagine:
        +    resource: "@LiipImagineBundle/Resources/config/routing.xml"

      ```

  - __\[Configuration\]__ `liip_imagine.filter_sets.route` option and sub options were removed.

  - __\[Configuration\]__ `liip_imagine.cache_prefix` option was removed.


## v1.0.0-alpha5

*View the [changelog entry](https://github.com/liip/LiipImagineBundle/blob/1.0/CHANGELOG.md#v100-alpha5) for the `1.0.0-alpha5` release.*

  - __\[Symfony\]__ Required minimum symfony version was updated to 2.3.

  - __\[Logger\]__ Symfony `LoggerInterface` was replaced with PSR-3 one.

  - __\[Cache\]__ New `isStored` method was added.

  - __\[Cache\]__ The method `ResolverInterface::getBrowserPath` was removed.

  - __\[Cache\]__ The method `ResolverInterface::store` accept `BinaryInterface` as first argument.

  - __\[Cache\]__ The method `ResolverInterface::store` return nothing.

  - __\[Cache\]__ The method `ResolverInterface::remove` return nothing.

  - __\[Cache\]__ The method `ResolverInterface::remove` takes required array of filter as first argument.

  - __\[Cache\]__ The method `ResolverInterface::remove` takes optional path as second argument.

  - __\[Cache\]__ The method `ResolverInterface::clean` was removed.

  - __\[Cache\]__ The method `ResolverInterface::resolve` takes path and filter as arguments.

  - __\[Cache\]__ The method `ResolverInterface::resolve` return absolute url of the cached image.

  - __\[Cache\]__ The method `CacheManager::resolve` may throw OutOfBoundsException if required resolver does not exist.

  - __\[Cache\]__ The method `CacheManager::resolve` return absolute url of the cached image.

  - __\[Cache\]__ The method `CacheManager::store` accept `BinaryInterface` as first argument.

  - __\[Cache\]__ The method `CacheManager::store` return nothing.

  - __\[Cache\]__ The method `CacheManager::clearResolversCache` was removed.

  - __\[Cache\]__ The method `CacheManager::getWebRoot` was removed.

  - __\[Cache\]__ The method `CacheManager::getBrowserPath` third argument was changed, now it is `runtimeConfig`.

  - __\[Cache\]__ The method `CacheManager::generateUrl` third argument was changed, now it is `runtimeConfig`.

  - __\[Cache\]__ `NoCacheResolver` renamed to `NoCacheWebPathResolver`.

  - __\[Cache\]__ `AbstractFilesystemResolver` was removed.

  - __\[Data Loader\]__ `LoaderInterface::find` now can return string or `BinaryInterface` instance.

  - __\[Data Loader\]__ `DataManager::find` now can return `BinaryInterface` instance only.

  - __\[Data Loader\]__ All data loaders moved to `Binary/Loader` folder.

  - __\[Data Loader\]__ Tag name `liip_imagine.data.loader` changed to `liip_imagine.binary.loader`

  - __\[Data Loader\]__ Parameter key `liip_imagine.data.loader.filesystem.class` changed to
    `liip_imagine.binary.loader.filesystem.class`

  - __\[Data Loader\]__ Parameter key `liip_imagine.data.loader.stream.class` changed to
    `liip_imagine.binary.loader.stream.class`

  - __\[Data Loader\]__ Service id `liip_imagine.data.loader.prototype.filesystem` changed to
    `liip_imagine.binary.loader.prototype.filesystem`

  - __\[Data Loader\]__ Service id `liip_imagine.data.loader.prototype.stream` changed to
    `liip_imagine.binary.loader.prototype.stream`

  - __\[Filter\]__ `FilterManager::applyFilter` now return instance of `BinaryInterface`.

  - __\[Filter\]__ `FilterManager::applyFilter` first argument was changed from Image instance to BinaryInterface one.

  - __\[Filter\]__ `FilterManager::get` was removed.

  - __\[Configuration\]__ `liip_imagine.filter_sets.path` option was removed.

  - __\[Configuration\]__ `liip_imagine.filter_sets.format` option was removed.

  - __\[Configuration\]__ `liip_imagine.cache_mkdir_mode` option was removed.

  - __\[Configuration\]__ `liip_imagine.web_root` option was removed.

  - __\[Configuration\]__ `liip_imagine.cache` default value was changed from `web_path`to `default`.

  - __\[Configuration\]__ `liip_imagine.formats` option was removed.

  - __\[Configuration\]__ `liip_imagine.data_root` option was removed.
