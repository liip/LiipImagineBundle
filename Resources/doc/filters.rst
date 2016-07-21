Filters
=======

Built-in Filters
----------------

The ``thumbnail`` filter
~~~~~~~~~~~~~~~~~~~~~~~~

The thumbnail filter, as the name implies, performs a thumbnail transformation
on your image.

The ``mode`` can be either ``outbound`` or ``inset``. Option ``inset`` does a
relative resize, where the height and the width will not exceed the values in
the configuration. Option ``outbound`` does a relative resize, but the image
gets cropped if width and height are not the same.

Given an input image sized 50x40 (width x height), consider the following
annotated configuration examples:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb_out:
                filters:
                    # Transforms 50x40 to 32x32, while cropping the width
                    thumbnail: { size: [32, 32], mode: outbound }
            my_thumb_in:
                filters:
                    # Transforms 50x40 to 32x26, no cropping
                    thumbnail: { size: [32, 32], mode: inset }


There is also an option ``allow_upscale`` (default: ``false``). By setting
``allow_upscale`` to ``true``, an image which is smaller than 32x32px in the
example above will be expanded to the requested size by interpolation of its
content. Without this option, a smaller image will be left as it. This means you
may get images that are smaller than the specified dimensions.

The ``relative_resize`` filter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``relative_resize`` filter may be used to ``heighten``, ``widen``,
``increase`` or ``scale`` an image with respect to its existing dimensions.
These options directly correspond to methods on Imagine's ``BoxInterface``.

Given an input image sized 50x40 (width, height), consider the following
annotated configuration examples:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_heighten:
                filters:
                    relative_resize: { heighten: 60 } # Transforms 50x40 to 75x60
            my_widen:
                filters:
                    relative_resize: { widen: 32 }    # Transforms 50x40 to 32x26
            my_increase:
                filters:
                    relative_resize: { increase: 10 } # Transforms 50x40 to 60x50
            my_widen:
                filters:
                    relative_resize: { scale: 2.5 }   # Transforms 50x40 to 125x100


The ``upscale`` filter
~~~~~~~~~~~~~~~~~~~~~~

It performs an upscale transformation on your image to increase its size to the
given dimensions:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    upscale: { min: [800, 600] }

The ``downscale`` filter
~~~~~~~~~~~~~~~~~~~~~~~~

It performs a downscale transformation on your image to reduce its size to the
given dimensions:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    downscale: { max: [1980, 1280] }

The ``crop`` filter
~~~~~~~~~~~~~~~~~~~

It performs a crop transformation on your image. The ``start`` option defines
the coordinates of the left-top pixel where the crop begins (the ``[0, 0]``
coordinates correspond to the top leftmost pixel of the original image). The
``size`` option defines in pixels the width and height (in this order) of the
area cropped:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    crop: { start: [10, 20], size: [120, 90] }

The ``strip`` filter
~~~~~~~~~~~~~~~~~~~~

It removes all profiles and comments from your image to reduce its file size
without degrading its quality. This filter provides no configuration options,
so you just need to enable it as follows:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    strip: ~

The ``background`` filter
~~~~~~~~~~~~~~~~~~~~~~~~~

It sets a background color for the image. The default color is white (``#FFF``):

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    background: { color: '#00FFFF' }

By default, the background color is only visible through the transparent sections
of the image (if any). However, if you provide a ``size`` option, a new image is
created (with the given size and color) and the original image is placed on top:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    background: { size: [1026, 684], position: center, color: '#fff' }

The ``watermark`` filter
~~~~~~~~~~~~~~~~~~~~~~~~

The watermark filter pastes a second image onto your image while keeping its
ratio. Configuration looks like this:

.. code-block:: yaml

    liip_image:
        filter_sets:
            my_image:
                filters:
                    watermark:
                        # Relative path to the watermark file (prepended with "%kernel.root_dir%/")
                        image: Resources/data/watermark.png
                        # Size of the watermark relative to the origin images size
                        size: 0.5
                        # Position: One of topleft,top,topright,left,center,right,bottomleft,bottom,bottomright
                        position: center

.. note::

    Please note that position of watermark filter is important. If you have some
    filters like ``crop`` after it is possible that watermark image will be
    cropped.

The ``auto_rotate`` filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

It rotates the image automatically to display it as correctly as possible. The
rotation to apply is obtained through the metadata stored in the EXIF data of
the original image. This filter provides no configuration options, so you just
need to enable it as follows:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    auto_rotate: ~

.. note::

    This filter should be called as early as possible to get better results.

The ``rotate`` filter
~~~~~~~~~~~~~~~~~~~~~

It rotates the image based on specified angle (in degrees). The value of the
``angle`` configuration option must be a positive integer or float number:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    rotate: { angle: 90 }

The ``interlace`` filter
~~~~~~~~~~~~~~~~~~~~~~~~

It modifies the way the image is loaded progressively:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    interlace:
                        # mode can be one of: 'none', 'line', 'plane' and 'partition'
                        mode: line

The ``grayscale`` filter
~~~~~~~~~~~~~~~~~~~~~~~~

It modifies the image colors by calculating the gray-value based on RGB:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    grayscale: ~

Load your Custom Filters
------------------------

The ImagineBundle allows you to load your own custom filter classes. The only
requirement is that each filter loader implements the following interface:
``Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface``.

To tell the bundle about your new filter loader, register it in the service
container and apply the ``liip_imagine.filter.loader`` tag to it (example here
in XML):

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        app.filter.my_custom_filter:
            class: AppBundle\Imagine\Filter\Loader\MyCustomFilterLoader
            tags:
                - { name: 'liip_imagine.filter.loader', loader: 'my_custom_filter' }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <service id="app.filter.my_custom_filter" class="AppBundle\Imagine\Filter\Loader\MyCustomFilterLoader">
            <tag name="liip_imagine.filter.loader" loader="my_custom_filter" />
        </service>

For more information on the service container, see the `Symfony Service Container`_
documentation.

You can now reference and use your custom filter when defining filter sets you'd
like to apply in your configuration:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_special_style:
                filters:
                    my_custom_filter: { }

For an example of a filter loader implementation, refer to
``Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader``.

Dynamic filters
---------------

With a custom controller action it is possible to dynamically modify the
configuration that will be applied to the image. Inside the controller you can
access ``FilterManager`` instance, pass configuration as third parameter of
``applyFilter`` method (for example based on information associated with the
image or whatever other logic you might want).

A simple example showing how to change the filter configuration dynamically.

.. code-block:: php

    public function filterAction($path, $filter)
    {
        if (!$this->cacheManager->isStored($path, $filter)) {
            $binary = $this->dataManager->find($filter, $path);

            $filteredBinary = $this->filterManager->applyFilter($binary, $filter, array(
                'filters' => array(
                    'thumbnail' => array(
                        'size' => array(300, 100)
                    )
                )
            ));

            $this->cacheManager->store($filteredBinary, $path, $filter);
        }

        return new RedirectResponse($this->cacheManager->resolve($path, $filter), Response::HTTP_MOVED_PERMANENTLY);
    }

.. note::

    The constant ``Response::HTTP_MOVED_PERMANENTLY`` was introduced in Symfony 2.4.
    Developers using older versions of Symfony, please replace the constant by ``301``.

Post-Processors
---------------

Filters allow modifying the image, but in order to modify the resulting binary
file created by filters, you can use post-processors. Post-processors must
implement ``Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface``.

``PostProcessorInterface::process`` method receives ``BinaryInterface`` -
basically, the file containing an image after all filters have been applied. It
should return the ``BinaryInterface`` as well.

Post-Processors, for this reason, may be safely chained. This is true even if they
operate on different mime-types, meaning that they are perfect for image-specific
optimisation techniques. A number of optimisers, lossy and loss-less, are provided
by default.

To tell the bundle about your post-processor, register it in the service
container and apply the ``liip_imagine.filter.post_processor`` tag to it:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        app.post_processor.my_custom_post_processor:
            class: AppBundle\Imagine\Filter\PostProcessor\MyCustomPostProcessor
            tags:
                - { name: 'liip_imagine.filter.post_processor', post_processor: 'my_custom_post_processor' }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <service id="app.post_processor.my_custom_post_processor" class="AppBundle\Imagine\Filter\PostProcessor\MyCustomPostProcessor">
            <tag name="liip_imagine.filter.post_processor" post_processor="my_custom_post_processor" />
        </service>

For more information on the service container, see the `Symfony Service Container`_
documentation.

You can now reference and use your custom filter when defining filter sets you'd
like to apply in your configuration:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_special_style:
                post_processors:
                    my_custom_post_processor: { }

For an example of a post processor implementation, refer to
``Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor``.

The ``JpegOptimPostProcessor`` can be used to provide lossless JPEG
optimization, which is good for you website loading speed. Parameters to configure
stripping of comment and exif data, max quality and progressive rendering may be
passed in optionally. In order to add lossless JPEG optimization to your filters,
use the following configuration:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [150, 150], mode: outbound }
                post_processors:
                    jpegoptim: { strip_all: true, max: 70, progressive: true }

Make sure that jpegoptim binary is installed on the system. If path to jpegoptim
binary is different from ``/usr/bin/jpegoptim``, adjust the path by overriding
parameters, for example:

.. code-block:: yaml

    parameters:
        liip_imagine.jpegoptim.binary: /usr/local/bin/jpegoptim

.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html

It is also possible to configure other defaults for the conversion process via parameters,
for example:

.. code-block:: yaml

    parameters:
        # When true, this passes down --strip-all to jpegoptim, which strips all markers from the output jpeg.
        liip_imagine.jpegoptim.stripAll: true

        # Sets the maxiumum image quality factor.
        liip_imagine.jpegoptim.max: null

        # When true, --all-progressive is passed to jpegoptim, which results in the output being a progressive jpeg.
        liip_imagine.jpegoptim.progressive: true

.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html


The ``OptiPngPostProcessor`` is also available by default and can be used just as jpegoptim.
Make sure that optipng binary is installed on the system and change the
``liip_imagine.optipng.binary`` in parameters if needed.

.. code-block:: yaml

    parameters:
        liip_imagine.optipng.binary: /usr/local/bin/optipng

.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html

It is also possible to configure other defaults for the conversion process via parameters,
for example:

.. code-block:: yaml

    parameters:
      # When true, this passes down --strip=all to optipng, which removes all metadata from the output image.
      liip_imagine.optipng.stripAll: true

      # The optimisation level to be used by optipng. Defaults to 7.
      liip_imagine.optipng.level: 7

.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html


The ``MozJpegPostProcessor`` can be used to provide safe lossy JPEG optimization.
Optionally, a quality parameter may be passed down to each instance.
More parameters may surface in the future.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [150, 150], mode: outbound }
                post_processors:
                    mozjpeg: {}
            my_other_thumb:
                filters:
                    thumbnail: { size: [150, 150], mode: outbound }
                post_processors:
                    mozjpeg: { quality: 90 }

Make sure that you have installed the mozjpeg tools on your system, and please adjust the
``liip_imagine.mozjpeg.binary`` in parameters if needed.

.. code-block:: yaml

    parameters:
        liip_imagine.mozjpeg.binary: /opt/mozjpeg/bin/cjpeg

.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html


The ``PngquantPostProcessor`` can be used to provide safe lossy PNG optimization.
Optionally, a quality parameter may be passed down to each instance.
More parameters may surface in the future.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [150, 150], mode: outbound }
                post_processors:
                    pngquant: {}
            my_other_thumb:
                filters:
                    thumbnail: { size: [150, 150], mode: outbound }
                post_processors:
                    pngquant: { quality: "80-100" }

Make sure that you have installed a recent version (at least 2.3) of pngquant on your system, and please adjust the
``liip_imagine.pngquant.binary`` in parameters if needed.

.. code-block:: yaml

    parameters:
        liip_imagine.pngquant.binary: /usr/bin/pngquant

.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html
