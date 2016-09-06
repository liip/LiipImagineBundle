
Filters
=======

Filters perform image transformation operations in the sequence they are defined
for the respective filter set.


Built-in Filters
----------------

A number of built-in filters are provided by default.

* :ref:`Thumbnail <filter-thumbnail>`
* :ref:`Relative Resize <filter-relative-resize>`
* :ref:`Scale <filter-scale>`
* :ref:`Up-Scale <filter-up-scale>`
* :ref:`Down-Scale <filter-down-scale>`
* :ref:`Crop <filter-crop>`
* :ref:`Strip <filter-strip>`
* :ref:`Background <filter-background>`
* :ref:`Watermark <filter-watermark>`
* :ref:`Auto Rotate <filter-auto-rotate>`
* :ref:`Rotate <filter-rotate>`
* :ref:`Interlace <filter-interlace>`
* :ref:`Grayscale <filter-grayscale>`


Thumbnail
~~~~~~~~~

.. _filter-thumbnail:

The ``thumbnail`` filter performs a thumbnail transformation on your image.

The ``mode`` can be either ``outbound`` or ``inset``. Option ``inset`` does a
relative resize, where the height and the width will not exceed the values in
the configuration. Option ``outbound`` does a relative resize, but the image
gets cropped if width and height are not the same.

Given an input image sized 50x40 (width x height), consider the following
annotated configuration examples.

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


Relative Resize
~~~~~~~~~~~~~~~

.. _filter-relative-resize:

The ``relative_resize`` filter may be used to ``heighten``, ``widen``,
``increase`` or ``scale`` an image with respect to its existing dimensions.
These options directly correspond to methods on Imagine's ``BoxInterface``.

Given an input image sized 50x40 (width, height), consider the following
annotated configuration examples.

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


Scale
~~~~~

.. _filter-scale:

The ``scale`` filter performs an upscale or downscale transformation on your
image to increase its size to the given dimensions or ratio.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    scale: { dim: [600, 750] } #or { to: 1.56 } -> Upscales to [936, 1170] | { to: 0.66 } -> Downscales to [396, 495]


Up-Scale
~~~~~~~~

.. _filter-up-scale:

The ``upscale`` filter performs an upscale transformation on your image to increase its size to the
given dimensions or ratio.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    upscale: { min: [800, 600] } #or { by: 0.7 } -> Upscales to [1360, 1020]


Down-Scale
~~~~~~~~~~

.. _filter-down-scale:

The ``downscale`` filter performs a downscale transformation on your image to reduce its size to the
given dimensions or ratio:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    downscale: { max: [1980, 1280] } #or { by: 0.6 } -> Downscales to [792, 512]


Crop
~~~~

.. _filter-crop:

The ``crop`` filter performs a crop transformation on your image. The ``start`` option defines
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


Strip
~~~~~

.. _filter-strip:

The ``strip`` filter removes all profiles and comments from your image to reduce its file size
without degrading its quality. This filter provides no configuration options,
so you just need to enable it.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    strip: ~


Background
~~~~~~~~~~

.. _filter-background:

The ``background`` filter adds a background color for the image. The default color is white (``#FFF``).

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    background: { color: '#00FFFF' }

By default, the background color is only visible through the transparent sections
of the image (if any). However, if you provide a ``size`` option, a new image is
created (with the given size and color) and the original image is placed on top.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    background: { size: [1026, 684], position: center, color: '#fff' }


Watermark
~~~~~~~~~

.. _filter-watermark:

The ``watermark`` filter pastes a second image onto your image while keeping its
ratio.

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


Auto Rotate
~~~~~~~~~~~

.. _filter-auto-rotate:

The ``auto_rotate`` filter rotates the image automatically to display it as correctly as possible. The
rotation to apply is obtained through the metadata stored in the EXIF data of
the original image. This filter provides no configuration options, so you just
need to enable it as follows.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    auto_rotate: ~

.. tip::

    This filter should be called as early as possible to get better results.


Rotate
~~~~~~

.. _filter-rotate:

The ``rotate`` filter rotates the image based on specified angle (in degrees). The value of the
``angle`` configuration option must be a positive integer or float number.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    rotate: { angle: 90 }


Interlace
~~~~~~~~~

.. _filter-interlace:

The ``interlace`` filter modifies the way the image is loaded progressively.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    interlace:
                        # mode can be one of: 'none', 'line', 'plane' and 'partition'
                        mode: line


Grayscale
~~~~~~~~~

.. _filter-grayscale:

The ``grayscale`` filter modifies the image colors by calculating the gray-value based on RGB.

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    grayscale: ~


Custom Filters
--------------

.. _filter-custom:

You can easily define your own, custom filters to perform any image
transformation operations required. Creating a custom filter begins
by creating a class that implements the ``Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface``
interface, as shown below.

.. code-block:: php

    interface LoaderInterface
    {
        public function load(ImageInterface $image, array $options = array());
    }

As defined in ``LoaderInterface``, the only required method is one named ``load``,
which is provided an instance of ``ImageInterface`` and an array of options, and
subsequently provides an instance of ``ImageInterface`` in return.

The following is a template for creating your own filter. You must provide
the implementation for the ``load`` method to create a valid filter.

.. code-block:: php

    namespace AppBundle\Imagine\Filter\Loader;

    use Imagine\Image\ImageInterface;
    use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

    class MyCustomFilter implements LoaderInterface
    {
        /**
         * @param ImageInterface $image
         * @param array          $options
         *
         * @return ImageInterface
         */
        public function load(ImageInterface $image, array $options = array())
        {
            /** @todo: implement */

            // return the image
            return $image;
        }
    }

Once you have defined your custom filter, you must define it as a service and tag it
with ``liip_imagine.filter.loader``.

.. note::

    For more information on the Service Container, reference the official
    `Symfony Service Container documentation`_.

To register ``AppBundle\Imagine\Filter\Loader\MyCustomFilter`` with the name
``my_custom_filter``, you would use the following configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        services:
            app.filter.my_custom_filter:
                class: AppBundle\Imagine\Filter\Loader\MyCustomFilter
                tags:
                    - { name: "liip_imagine.filter.loader", loader: my_custom_filter }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="app.filter.my_custom_filter" class="AppBundle\Imagine\Filter\Loader\MyCustomFilter">
            <tag name="liip_imagine.filter.loader" loader="my_custom_filter" />
        </service>

You can now reference and use your custom filter when defining filter sets in your configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_special_style:
                filters:
                    my_custom_filter: { }


Dynamic filters
---------------

.. _filter-dynamic:

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

.. tip::

    The constant ``Response::HTTP_MOVED_PERMANENTLY`` was introduced in Symfony 2.4.
    Developers using older versions of Symfony, please replace the constant by ``301``.


.. _`Symfony Service Container documentation`: http://symfony.com/doc/current/book/service_container.html
