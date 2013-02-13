# Configuration

The default configuration for the bundle looks like this:

``` yaml
liip_imagine:
    driver:               gd
    web_root:             %kernel.root_dir%/../web
    data_root:            %liip_imagine.web_root%
    cache_mkdir_mode:     0777
    cache_prefix:         /media/cache
    cache:                web_path
    cache_clearer:        true
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

 - `web_root` - must be the absolute path to you application's web root. This
    is used to determine where to put generated image files, so that apache
    will pick them up before handing the request to Symfony2 next time they
    are requested.

    default: `%kernel.root_dir%/../web`

 - `data_root` - the absolute path to the location that original files should
    be sourced from. This option only changes the standard filesystem loader.

    default: `%kernel.root_dir%/../web`

 - `cache_mkdir_mode` - permissions to set on generated cache directories.
    Must be specified as an octal number, which means it should begin with a
    leading zero. mode is ignored on Windows.

    default: `0777`

 - `cache_prefix` - this is also used in the path for image generation, so
    as to not clutter your web root with cached images. For example by default,
    the images would be written to the `web/media/cache/` directory.

    default: `/media/cache`

 - `cache` - default cache resolver

    default: web_path (which means the standard web_path resolver is used)

 - `cache_clearer` - Whether or not to clear the image cache when the `kernel.cache_clearer` event occurs.
    This option doesn't have any effect in symfony < 2.1

    default: true

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
