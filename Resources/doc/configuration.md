# Configuration

The default configuration for the bundle looks like this:

``` yaml
liip_imagine:

    resolvers:
        default:
            web_path:
              web_root: ~ # %kernel.root_dir%/../web
              cache_prefix: ~ # media/cache


    loaders:
        default:
            filesystem:
                data_root: ~  # %kernel.root_dir%/../web/

    driver:               gd
    cache:                default
    data_loader:          default
    default_image:        null
    controller:
        filter_action:         liip_imagine.controller:filterAction
        filter_runtime_action: liip_imagine.controller:filterRuntimeAction
    filter_sets:

        # Prototype
        name:
            path:                 ~
            quality:              100
            animated:             false
            format:               ~
            cache:                ~
            data_loader:          ~
            default_image:        null
            controller:           ~
            route:                []
            filters:

                # Prototype
                name:                 []
```

There are several configuration options available:

 - `cache` - default cache resolver

    default: web_path (which means the standard web_path resolver is used)

 - `data_loader` - name of a custom data loader

    default: filesystem (which means the standard filesystem loader is used)

 - `controller`
         - `filter_action` - name of the controller action to use in the route loader

            default: liip_imagine.controller:filterAction

        - `filter_runtime_action` - name of the controller action to use in the route loader for runtimeconfig images

            default: liip_imagine.controller:filterRuntimeAction

 - `driver` - one of the three drivers: `gd`, `imagick`, `gmagick`

    default: `gd`

 - `filter_sets` - specify the filter sets that you want to define and use

Each filter set that you specify has the following options:

 - `filters` - determine the type of filter to be used (refer to *Filters* section for more information)
    and options that should be passed to the specific filter type
 - `path` - used in place of the filter name to determine the path in combination with the global `cache_prefix`
 - `quality` - override the default quality of 100 for the generated images
 - `cache` - override the default cache setting
 - `data_loader` - override the default data loader
 - `controller`
    - `filter_action` - override the default controller action
    - `filter_runtime_action` - override the default controller action for runtime config
 - `route` - optional list of route requirements, defaults and options using in the route loader. Add array with keys 'requirements', 'defaults' or 'options'.

    default: empty array

 - `format` - hardcodes the output format (aka the requested format is ignored)
 - `animated` - support for resizing animated gif (currently not supported by Imagine (PR pending))

[Back to the index](index.md)
