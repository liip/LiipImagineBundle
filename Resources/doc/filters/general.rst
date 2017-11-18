

General Filters
===============

.. _filter-background:

Background
----------

The built-in ``background`` filter performs layer transformations
(which includes creating and mergin layer operations). This
filter exposes a number of `background options`_ which may be used
to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_background_filter"
            my_background_filter:
                filters:

                    # use and setup the "background" filter
                    background:

                        # set the background color to #00ffff
                        color: '#00ffff'

                        # set a size different from the input image
                        size: [1026, 684]

                        # center input image on the newly created background
                        position: center


.. note::

    The background color is only visible through transparent image sections (if
    any), unless a **size** option is provided, in which case a new image is
    created and the input image is placed on top according to the **position** option.


Background Options
~~~~~~~~~~~~~~~~~~

:strong:`color:` ``string``
    Sets the background color HEX value. The default color is white (``#fff``).

:strong:`size:` ``int[]``
    Sets the generated background size as an integer array containing the dimensions
    as width and height values.

:strong:`position:` ``string``
    Sets the position of the input image on the newly created background image. Valid
    values: ``topleft``, ``top``, ``topright``, ``left``, ``center``, ``right``, ``bottomleft``,
    ``bottom``, and ``bottomright``.


.. _filter-grayscale:

Grayscale
---------

The built-in ``grayscale`` filter performs color transformations
(which includes gray value calculations). This
filter does not expose any options which may be used
to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_grayscale_filter"
            my_grayscale_filter:
                filters:

                    # use and setup the "grayscale" filter
                    grayscale: ~


.. _filter-interlace:

Interlace
---------

The built-in ``interlace`` filter performs file transformations
(which includes modifying the encoding method). This
filter exposes a number of `interlace options`_ which may be used
to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_interlace_filter"
            my_interlace_filter:
                filters:

                    # use and setup the "interlace" filter
                    interlace:

                        # set the interlace mode to line
                        mode: line


Interlace Options
~~~~~~~~~~~~~~~~~

:strong:`mode:` ``string``
    Sets the interlace mode to encode the file with. Valid values: ``none``, ``line``,
    ``plane``, and ``partition``.


.. _filter-resample:

Resample
--------

The built-in ``resample`` filter provides a resampling transformation by allows you to
change the resolution of an image. This filter exposes a number of `resample options`_ which
may be used to configure its behavior.

.. tip::

    Resampling changes the image resolution (also known as "pixel density") of an image
    and is useful when you need to present different versions of an image dependent on
    the user's screen density. For example, you may need to provide a "normal" and a
    "retina" variant.

    The use of "resolution" is not to be confused with "dimensions". This filter does not
    affect the dimentions of an image, only the pixel density.


Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_resample_filter"
            my_resample_filter:
                filters:

                    # use and setup the "resample" filter
                    resample:

                        # set the unit to use for pixel density
                        unit: ppi

                        # set the horizontal pixel density
                        x: 72

                        # set the vertical pixel density
                        y: 72

                        # set the resampling filter
                        filter: lanczos

                        # set the temporary path to use for resampling work
                        tmp_dir: /my/custom/temporary/directory/path


Resample Options
~~~~~~~~~~~~~~~~

:strong:`unit:` ``string``
    Sets the unit to use for pixel density, either "pixels per inch" or "pixels per centimeter".
    Valid values: ``ppi`` and ``ppc``.

:strong:`x:` ``int|float``
    Sets the horizontal (x) pixel density to resample the image to.

:strong:`y:` ``int|float``
    Sets the vertical (y) pixel density to resample the image to.

:strong:`filter:` ``string``
    Sets the optional filter to use during the resampling operation. It must be a string resolvable
    as a constant from `Imagine\Image\ImageInterface`_ (you may omit the ``FILTER_`` prefix)
    or a valid fully qualified constant. By default it is set to ``FILTER_UNDEFINED``.

:strong:`tmp_dir:` ``string``
    Sets the optional temporary work directory. This filter requires a temporary location to save
    out and read back in the image binary, as these operations are requires to resample an image.
    By default, it is set to the value of the `sys_get_temp_dir()`_ function.


.. _filter-strip:

Strip
-----


The built-in ``strip`` filter performs file transformations
(which includes metadata removal). This
filter does not exposes any options which may be used
to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_strip_filter"
            my_strip_filter:
                filters:

                    # use and setup the "strip" filter
                    strip: ~


.. _filter-watermark:

Watermark
---------

The built-in ``watermark`` filter adds a watermark to an existing image
(which includes creating and merging image operations). This
filter exposes a number of `watermark options`_ which may be used
to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_watermark_filter"
            my_watermark_filter:
                filters:

                    # use and setup the "watermark" filter
                    watermark:

                        # path to the watermark file (prepended with "%kernel.root_dir%")
                        image: Resources/data/watermark.png

                        # size of the water mark relative to the input image
                        size: 0.5

                        # set the position of the watermark
                        position: center


Watermark Options
~~~~~~~~~~~~~~~~~

:strong:`image:` ``string``
    Sets the location of the watermark image. The value of this option is prepended
    with the resolved value of the ``%kernel.root_dir%`` parameter.

:strong:`size:` ``float``
    Sets the size of the watermark as a relative ration, relative to the original
    input image.

:strong:`position:` ``string``
    Sets the position of the watermark on the input image. Valid values: ``topleft``,
    ``top``, ``topright``, ``left``, ``centerleft``,  ``center``, ``centerright``, ``right``, ``bottomleft``, ``bottom``, and
    ``bottomright``.

.. caution::

    The **position** option and **ordering** for this filter is significant.
    For example, calling a ``crop`` after this filter could unintentionally
    remove the watermark entirely from the final image.


.. _`Imagine\Image\ImageInterface`: https://imagine.readthedocs.io/en/master/_static/API/Imagine/Image/ImageInterface.html
.. _`sys_get_temp_dir()`: http://php.net/manual/en/function.sys-get-temp-dir.php
