Filters
=======

Built-in Filters
----------------

The ``thumbnail`` filter
~~~~~~~~~~~~~~~~~~~~~~

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
                    thumbnail: { size: [32, 32], mode: outbound } # Transforms 50x40 to 32x32, while cropping the width
            my_thumb_in:
                filters:
                    thumbnail: { size: [32, 32], mode: inset } # Transforms 50x40 to 32x26, no cropping


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

The upscale filter, as the name implies, performs a upscale transformation
on your image. Configuration looks like this:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    upscale: { min: [800, 600] }

The ``crop`` filter
~~~~~~~~~~~~~~~~~~~

The crop filter, as the name implies, performs a crop transformation on your
image. Configuration looks like this:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    crop: { start: [10, 20], size: [120, 90] }

The ``strip`` filter
~~~~~~~~~~~~~~~~~~~~

The strip filter removes all profiles and comments from your image.
Configuration looks like this:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    strip: ~

The ``background`` filter
~~~~~~~~~~~~~~~~~~~~~~~~~

The background filter sets a background color for your image, default is white
(#FFF). Configuration looks like this:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    background: { color: '#00FFFF' }

If you provide a ``size`` it will create a new image (this size and given
color), and apply the original image on top:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    background: { size: [1026, 684], color: '#fff' }

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

The auto_rotate filter rotates the image based on its EXIF data. **(this filter
should be called as early as possible)** Configuration looks like this:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    auto_rotate: ~

The ``rotate`` filter
~~~~~~~~~~~~~~~~~~~~~

The rotate filter rotates the image based on specified angle (in degrees).
Configuration looks like this:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    rotate: { angle: 90 }

The ``interlace`` filter
~~~~~~~~~~~~~~~~~~~~~~~~

Set progressive loading on the image. Configuration looks like this:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    interlace:
                        # mode can be one of none,line,plane,partition
                        mode: line

Load your Custom Filters
------------------------

The ImagineBundle allows you to load your own custom filter classes. The only
requirement is that each filter loader implement the following interface:
``Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface``.

To tell the bundle about your new filter loader, register it in the service
container and apply the ``liip_imagine.filter.loader`` tag to it (example here
in XML):

.. code-block:: xml

    <service id="liip_imagine.filter.loader.my_custom_filter" class="Acme\ImagineBundle\Imagine\Filter\Loader\MyCustomFilterLoader">
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

        return new RedirectResponse($this->cacheManager->resolve($path, $filter), 301);
    }

Post-Processors
---------------

Filters allow modifying the image, but in order to modify the resulting binary
file created by filters, you can use post-processors Post-processors should
implement ``Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface``.

``PostProcessorInterface::process`` method receives ``BinaryInterface`` -
basically, the file containing an image after all filters have been applied. It
should return the ``BinaryInterface`` as well.

To tell the bundle about your post-processor, register it in the service
container and apply the ``liip_imagine.filter.post_processor`` tag to it
(example here in XML):

.. code-block:: xml

    <service id="liip_imagine.filter.post_processor.my_custom_post_processor" class="Acme\ImagineBundle\Imagine\Filter\PostProcessor\MyCustomPostProcessor">
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

The ``JpegOptimPostProcessor`` can be used to provide lossless jpeg
optimization, which is good for you website loading speed. In order to add
lossless jpeg optimization to your filters, use the following configuration:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [150, 150], mode: outbound }
                post_processors:
                    jpegoptim: {}

Make sure that jpegoptim binary is installed on the system. If path to jpegoptim
binary is different from ``/usr/bin/jpegoptim``, adjust the path by overriding
parameters, for example:

.. code-block:: yaml

    parameters:
        liip_imagine.jpegoptim.binary: /usr/local/bin/jpegoptim

.. _`Symfony Service Container`: http://symfony.com/doc/current/book/service_container.html
