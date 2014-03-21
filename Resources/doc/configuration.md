# Configuration

The default configuration for the bundle looks like this:

``` yaml
liip_imagine:
# add default loader
#    resolvers:
#        default:
#            web_path: ~

    driver:               gd
    data_root:            %liip_imagine.web_root%
    cache:                default
    data_loader:          filesystem
    controller_action:    liip_imagine.controller:filterAction
    formats:              []
    filter_sets:

        # Prototype
        name:
            path:                 ~
            quality:              100
            format:               ~
            cache:                ~
            data_loader:          ~
            controller_action:    ~
            route:                []
            filters:

                # Prototype
                name:                 []
```

There are several configuration options available:


 - `data_root` - the absolute path to the location that original files should
    be sourced from. This option only changes the standard filesystem loader.

    default: `%kernel.root_dir%/../web`

 - `cache` - default cache resolver

    default: web_path (which means the standard web_path resolver is used)

 - `data_loader` - name of a custom data loader

    default: filesystem (which means the standard filesystem loader is used)

 - `controller_action` - name of the controller action to use in the route loader

    default: liip_imagine.controller:filterAction

 - `driver` - one of the three drivers: `gd`, `imagick`, `gmagick`

    default: `gd`

 - `formats` - optional list of image formats to which images may be converted to.

 - `filter_sets` - specify the filter sets that you want to define and use

Each filter set that you specify has the following options:

 - `filters` - determine the type of filter to be used (refer to *Filters* section for more information)
    and options that should be passed to the specific filter type
 - `path` - used in place of the filter name to determine the path in combination with the global `cache_prefix`
 - `quality` - override the default quality of 100 for the generated images
 - `cache` - override the default cache setting
 - `data_loader` - override the default data loader
 - `controller_action` - override the default controller action
 - `route` - optional list of route requirements, defaults and options using in the route loader. Add array with keys 'requirements', 'defaults' or 'options'.

    default: empty array

 - `format` - hardcodes the output format (aka the requested format is ignored)

[Back to the index](index.md)
