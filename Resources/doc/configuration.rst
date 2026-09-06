

Configuration
=============

The default configuration for the bundle looks like this:

.. code-block:: yaml

    # app/config/config.yml
    liip_imagine:

        resolvers:
            default:
                web_path:
                    web_root: ~ # %kernel.project_dir%/public (%kernel.project_dir%/web for Symfony < 4.0.0)
                    cache_prefix: ~ # media/cache

        loaders:
            default:
                filesystem:
                    data_root: ~  # %kernel.project_dir%/public (%kernel.project_dir%/web for Symfony < 4.0.0)

        driver:               gd
        cache:                default
        data_loader:          default
        default_image:        null
        twig:
            mode:             legacy
        default_filter_set_settings:
            quality:              100
            jpeg_quality:         ~
            png_compression_level:  ~
            png_compression_filter: ~
            animated:             false
            format:               ~
            cache:                ~
            data_loader:          ~
            default_image:        null
            filters: []
            post_processors: []
        controller:
            filter_action:          liip_imagine.controller::filterAction
            filter_runtime_action:  liip_imagine.controller::filterRuntimeAction
            redirect_response_code: 302
            debug:                  ~ # %kernel.debug%
        webp:
            generate:    false
            quality:     100
            cache:       ~
            data_loader: ~
            post_processors: []
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
  (which means the standard filesystem loader is used). Built-in loaders include:
  ``filesystem``, ``chain``, ``flysystem``, ``stream``, and ``asset_mapper``.
* ``default_image`` - image served when the requested one can not be delivered, for every filter set
  that does not define its own. Default value: ``null``. See `Default image`_ below.
* ``twig.mode`` - Twig filter integration. ``none`` disables the twig filters, ``lazy`` enables
  Twig using the Twig runtime for lazy loading. The default value is ``legacy`` and enables the
  old Twig integration that is loaded on each request. Version 3 will drop ``legacy`` and default
  to ``lazy``.
  The twig filter automatically picks up the ``framework.assets.version`` configuration. You can
  overwrite the version with the ``twig.assets_version`` option. See :doc:`asset-versioning` for
  more information.
* ``controller``
    * ``filter_action`` - name of the controller action to use in the route loader.
      Default value: ``liip_imagine.controller:filterAction``
    * ``filter_runtime_action`` - name of the controller action to use in the route
      loader for runtimeconfig images. Default value: ``liip_imagine.controller:filterRuntimeAction``
    * ``redirect_response_code`` - The HTTP redirect response code to return from the imagine controller,
      one of ``201``, ``301``, ``302``, ``303``, ``307``, or ``308``. Default value: ``302``
      See :doc:`optimizations/avoid-redirects` if you want to change this configuration.
    * ``debug`` - Whether an image that can not be generated is reported as an error. Default value:
      ``%kernel.debug%``. See `Default image`_ below.
* ``webp``
    * ``generate`` - enabling the generation a copy of the image in the WebP format.
    * ``quality`` - override the quality from filter option.
    * ``cache`` - default cache resolver. Default value: ``web_path`` (which means
      the standard web_path resolver is used)
    * ``data_loader`` - name of a custom data loader. Default value: ``filesystem``
      (which means the standard filesystem loader is used). Built-in loaders include:
      ``filesystem``, ``chain``, ``flysystem``, ``stream``, and ``asset_mapper``.
    * ``post_processors`` - sets post-processors to be applied on filtered image
      (see Post-Processors section in the :doc:`filters chapter <filters>` for details).
* ``driver`` - one of the drivers: ``gd``, ``imagick``, ``gmagick``, ``vips``.
  Default value: ``gd``
  * If you want to use vips, you need to additionally require ``rokka/imagine-vips``
* ``default_filter_set_settings`` - specify the default values that will be inherit for any set defined in
  ``filter_sets``. These values will be overridden if they are specified in the each set. In case of ``filters`` and
  ``post_processors``, the specified values will be merged with the default ones.
* ``filter_sets`` - specify the filter sets that you want to define and use.

Each filter set that you specify has the following options:

* ``filters`` - determine the type of filter to be used (refer to *Filters* section
  for more information) and options that should be passed to the specific filter type.
* ``post_processors`` - sets post-processors to be applied on filtered image
  (see Post-Processors section in the :doc:`filters chapter <filters>` for details).
* ``jpeg_quality`` - override the quality for jpeg images (this overrides the
  ``quality`` option above)
* ``png_compression_level`` - set the compression level for png images (0-9)
  (this overrides the ``quality`` option above)
* ``png_compression_filter`` - set the compression filter for png images (see the
  ``filters`` parameter for ``imagepng`` function in `PHP manual`_ for more details)
* ``cache`` - override the default cache setting.
* ``data_loader`` - override the default data loader.
* ``default_image`` - override the default image of this filter set. See `Default image`_ below.
* ``route`` - optional list of route requirements, defaults and options using in
  the route loader. Add array with keys ``requirements``, ``defaults`` or ``options``.
  Default value: empty array.
* ``format`` - hardcodes the output format (which means that the requested format
  is ignored).
* ``animated`` - support for resizing animated gif (currently not supported by
  Imagine (PR pending))


Default image
-------------

When the imagine controller can not deliver an image, it redirects to the ``default_image`` of the
requested filter set, falling back to the global ``default_image``. Both are URLs to an existing
image, and both default to ``null``, which disables the mechanism:

.. code-block:: yaml

    liip_imagine:
        default_image: /images/placeholder.png
        filter_sets:
            avatar:
                default_image: /images/anonymous.png
                filters:
                    thumbnail: { size: [100, 100], mode: outbound }

Which failures are replaced by that image depends on ``controller.debug``:

* A source image that can not be loaded always leads to the default image. This is the common case
  of a path that points at nothing.
* A filter set that does not exist, or an image that the driver fails to process, leads to the
  default image only when ``controller.debug`` is disabled, which by default means outside of the
  Symfony debug mode. The exception is then logged with the ``warning`` level, so that a broken
  filter or an unsupported source file does not go unnoticed.

In debug mode those two remain exceptions, so that a mistake in the configuration or a file the
driver chokes on is visible while developing instead of silently turning into a placeholder.

.. _`PHP Manual`: https://php.net/imagepng
