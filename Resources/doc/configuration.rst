Configuration
=============

The default configuration for the bundle looks like this:

.. code-block:: yaml

    # app/config/config.yml
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
                quality:              100
                jpeg_quality:         ~
                png_compression_level:  ~
                png_compression_filter: ~
                animated:             false
                format:               ~
                cache:                ~
                data_loader:          ~
                default_image:        null
                filters:

                    # Prototype
                    name:                 []

                post_processors:

                    # Prototype
                    name:                 []

There are several configuration options available:

* ``cache`` - default cache resolver. Default value: ``web_path`` (which means
  the standard web_path resolver is used)
* ``data_loader`` - name of a custom data loader. Default value: ``filesystem``
  (which means the standard filesystem loader is used).
* ``controller``
    * ``filter_action`` - name of the controller action to use in the route loader.
      Default value: ``liip_imagine.controller:filterAction``
    * ``filter_runtime_action`` - name of the controller action to use in the route
      loader for runtimeconfig images. Default value: ``liip_imagine.controller:filterRuntimeAction``
* ``driver`` - one of the three drivers: ``gd``, ``imagick``, ``gmagick``.
  Default value: ``gd``
* ``filter_sets`` - specify the filter sets that you want to define and use.

Each filter set that you specify has the following options:

* ``filters`` - determine the type of filter to be used (refer to *Filters* section
  for more information) and options that should be passed to the specific filter type.
* ``post_processors`` - sets post-processors to be applied on filtered image
  (see Post-Processors section in the :doc:`filters chapter <filters>` for details).
* ``quality`` - override the default quality of 100 for the generated images. **(deprecated)**
* ``jpeg_quality`` - override the quality for jpeg images (this overrides the
  ``quality`` option above)
* ``png_compression_level`` - set the compression level for png images (0-9)
  (this overrides the ``quality`` option above)
* ``png_compression_filter`` - set the compression filter for png images (see the
  ``filters`` parameter for ``imagepng`` function in `PHP manual`_ for more details)
* ``cache`` - override the default cache setting.
* ``data_loader`` - override the default data loader.
* ``route`` - optional list of route requirements, defaults and options using in
  the route loader. Add array with keys ``requirements``, ``defaults`` or ``options``.
  Default value: empty array.
* ``format`` - hardcodes the output format (which means that the requested format
  is ignored).
* ``animated`` - support for resizing animated gif (currently not supported by
  Imagine (PR pending))

.. _`PHP Manual`: http://php.net/imagepng
