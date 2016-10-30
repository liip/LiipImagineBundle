

Size Filters
============


.. _filter-thumbnail:

Thumbnails
----------

The built-in ``thumbnail`` filter performs thumbnail transformations
(which includes scaling and potentially cropping operations). This
filter exposed a number of `thumbnail options`_ which may be used
to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_thumb_filter"
            my_thumb_filter:
                filters:

                    # use and setup the "thumbnail" filter
                    thumbnail:

                        # set the thumbnail size to "32x32" pixels
                        size: [32, 32]

                        # crop the input image, if required
                        mode: outbound

.. seealso::

    More examples are available in the
    :ref:`Basic Usage: Create Thumbnails <usage-create-thumbnails>` chapter.


Thumbnail Options
~~~~~~~~~~~~~~~~~

:strong:`mode:` ``string``
    Sets the desired resize method: ``'outbound'`` crops the image as required, while
    ``'inset'`` performs a non-cropping relative resize.

:strong:`size:` ``int[]``
    Sets the generated thumbnail size as an integer array containing the dimensions
    as width and height values.

:strong:`allow_upscale:` ``bool``
    Toggles allowing image up-scaling when the image is smaller than the desired
    thumbnail size.


.. _filter-crop:

Cropping Images
---------------

The built-in ``crop`` filter performs sizing transformations (which
includes cropping operations). This filter exposed a number of
`crop options`_ which may be used to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_crop_filter"
            my_crop_filter:
                filters:

                    # use and setup the "crop" filter
                    crop:

                        # set the size of the cropping area
                        size: [ 300, 600 ]

                        # set the starting coordinates of the crop
                        start: [ 040, 160 ]


Crop Options
~~~~~~~~~~~~

:strong:`size:` ``int[]``
    Sets the crop size as an integer array containing the dimensions as width and
    height values.

:strong:`start:` ``int[]``
    Sets the top, left-post anchor coordinates where the crop operation starts.


.. _filter-relative-resize:

Relative Resize
---------------

The built-in ``relative_resize`` filter performs sizing transformations (specifically
relative resizing). This filter exposed a number of `relative resize options`_ which
may be used to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our first filter set "my_heighten_filter"
            my_heighten_filter:
                filters:

                    # use and setup the "relative_resize" filter
                    relative_resize:

                        # given 50x40px, output 75x60px using "heighten" option
                        heighten: 60

            # name our second filter set "my_widen_filter"
            my_widen_filter:
                filters:

                    # use and setup the "relative_resize" filter
                    relative_resize:

                        # given 50x40px, output 32x26px using "widen" option
                        widen: 32

            # name our second filter set "my_increase_filter"
            my_increase_filter:
                filters:

                    # use and setup the "relative_resize" filter
                    relative_resize:

                        # given 50x40px, output 60x50px, using "increase" option
                        increase: 10

            # name our second filter set "my_scale_filter"
            my_scale_filter:
                filters:

                    # use and setup the "relative_resize" filter
                    relative_resize:

                        # given 50x40px, output 125x100px using "scale" option
                        scale: 2.5


.. tip::

    The "relative resize" filter options map directly to the methods of the
    `BoxInterface`_ interface provided by the `Imagine Library`_.


Relative Resize Options
~~~~~~~~~~~~~~~~~~~~~~~

:strong:`heighten:` ``float``
    Sets the "desired height" which initiates a proportional scale operation that up- or
    down-scales until the image height matches this value.

:strong:`widen:` ``float``
    Sets the "desired width" which initiates a proportional scale operation that up- or
    down-scales until the image width matches this value.

:strong:`increase:` ``float``
    Sets the "desired additional size" which initiates a scale operation computed by
    adding this value to all image sides.

:strong:`scale:` ``float``
    Sets the "ratio multiple" which initiates a proportional scale operation computed
    by multiplying all image sides by this value.


.. _filter-scale:

Scale
-----

The built-in ``scale`` filter performs sizing transformations (specifically
image scaling). This filter exposed a number of `scale options`_ which
may be used to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our first filter set "my_ratio_down_scale_filter"
            my_ratio_down_scale_filter:
                filters:

                    # use and setup the "scale" filter
                    scale:

                        # given 1920x1600px -> output 960x800px (relative down-scale)
                        to: 0.5

            # name our first filter set "my_ratio_up_scale_filter"
            my_ratio_up_scale_filter:
                filters:

                    # use and setup the "scale" filter
                    scale:

                        # given 1920x1600px -> output 5760x3200px (relative up-scale)
                        to: 2

            # name our third filter set "my_dim_down_scale_filter"
            my_dim_down_scale_filter:
                filters:

                    # use and setup the "scale" filter
                    scale:

                        # input 1200x1600px -> output 750x1000px (relative down-scale)
                        dim: [ 800, 1000 ]

            # name our fourth filter set "my_dim_up_scale_filter"
            my_dim_up_scale_filter:
                filters:

                    # use and setup the "scale" filter
                    scale:

                        # input 300x900px -> output 900x2700px (relative up-scale)
                        dim: [ 1200, 2700 ]


Scale Options
~~~~~~~~~~~~~

:strong:`dim:` ``int[]``
    Sets the "desired dimensions" as an array containing a width and height integer, from
    which a relative resize is performed within these constraints.

:strong:`to:` ``float``
    Sets the "ratio multiple" which initiates a proportional scale operation computed
    by multiplying all image sides by this value.


.. _filter-down-scale:

Down Scale
----------

The built-in ``downscale`` filter performs sizing transformations (specifically
image down-scaling). This filter exposed a number of `down scale options`_ which
may be used to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our first filter set "my_max_down_scale_filter"
            my_max_down_scale_filter:
                filters:

                    # use and setup the "downscale" filter
                    downscale:

                        # input 3960x2560px -> output 1980x1280px
                        max: [1980, 1280]

            # name our second filter set "my_by_down_scale_filter"
            my_by_down_scale_filter:
                filters:

                    # use and setup the "downscale" filter
                    downscale:

                        # input 1980x1280px -> output 792x512px
                        by: 0.6


Down Scale Options
~~~~~~~~~~~~~~~~~~

:strong:`max:` ``int[]``
    Sets the "desired max dimensions" as an array containing a width and height integer, from
    which a down-scale is performed to meet the passed constraints.

:strong:`by:` ``float``
    Sets the "ratio multiple" which initiates a proportional scale operation computed
    by multiplying all image sides by this value.


.. _filter-up-scale:

Up Scale
--------

The built-in ``upscale`` filter performs sizing transformations (specifically
image up-scaling). This filter exposed a number of `up scale options`_ which
may be used to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our first filter set "my_min_up_scale_filter"
            my_min_up_scale_filter:
                filters:

                    # use and setup the "upscale" filter
                    upscale:

                        # input 1980x1280px -> output 3960x2560px
                        min: [3960, 2560]

            # name our second filter set "my_by_up_scale_filter"
            my_by_up_scale_filter:
                filters:

                    # use and setup the "upscale" filter
                    upscale:

                        # input 800x600px -> output 1360x1020px
                        by: 0.7


Up Scale Options
~~~~~~~~~~~~~~~~

:strong:`min:` ``int[]``
    Sets the "desired min dimensions" as an array containing a width and height integer, from
    which an up-scale is performed to meet the passed constraints.

:strong:`by:` ``float``
    Sets the "ratio multiple" which initiates a proportional scale operation computed
    by multiplying all image sides by this value.


.. _`BoxInterface`: http://imagine.readthedocs.io/en/latest/usage/coordinates.html#boxinterface
.. _`Imagine Library`: http://imagine.readthedocs.io/en/latest/
