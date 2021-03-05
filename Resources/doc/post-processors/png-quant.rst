
.. _post-processor-pngquant:

PNG Quant
=========

The ``PngquantPostProcessor`` is a built-in post-processor that performs a number of
*safe, lossy* optimizations on *PNG* encoded images.

To add this post-processor to the filter set created in the
:ref:`thumbnail usage example <usage-create-thumbnails>` use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_thumb:
                filters:
                    thumbnail: { size: [120, 90], mode: outbound }
                    background: { size: [124, 94], position: center, color: '#000' }
                post_processors:
                    pngquant: { quality: "75-85" }

This configuration sets a quality factor range of 75 to 85 for the resulting image binary.

.. note::

    The default executable path is ``/usr/bin/pngquant``. If installed elsewhere
    on your system, you must set the ``liip_imagine.pngquant.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.pngquant.binary: /your/custom/path/to/pngquant


Options
-------

**quality:** ``int|int[]``
    When set to an ``int`` this sets the maximum image quality level. When set to an ``int[]`` (such as ``[60,80]``) the
    first array ``int`` is used to define the lowest acceptable quality level and the second to define the maximum quality
    level (in this mode, the executable will use the least amount of colors required to meet or exceed the maximum quality,
    but if the conversion results in a quality below the minimum quality the converted file will be discarded and the
    original one used instead).

**speed:** ``int``
    The speed/quality trade-off value to use. Valid values: ``1`` (slowest/best) through ``11`` (fastest/worst).

**dithering:** ``bool|float``
    When set to ``false`` the Floyd-Steinberg dithering algorithm is completely disabled. Otherwise, when a ``float``,
    the dithering level is set.

Parameters
----------

**liip_imagine.pngquant.binary:** ``string``
    Sets the location of the ``pnquant`` executable. Default is ``/usr/bin/pngquant``.
