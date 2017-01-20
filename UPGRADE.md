# Upgrade

## 1.7.x

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
