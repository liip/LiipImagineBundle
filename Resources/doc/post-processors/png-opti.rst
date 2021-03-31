
.. _post-processor-optipng:

Opti PNG
========

The ``OptiPngPostProcessor`` is a built-in post-processor that performs a number of
*lossless* optimizations on *PNG* encoded images.

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
                    optipng: { strip_all: true, level: 5 }

This configuration enables metadata stripping, and sets a maximum optimization factor of 5
for the resulting image binary.

.. note::

    The default executable path is ``/usr/bin/optipng``. If installed elsewhere
    on your system, you must set the ``liip_imagine.optipng.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.optipng.binary: /your/custom/path/to/optipng


Options
-------

**level:** ``int``
    Sets the image optimization level. Valid values are integers between ``0`` and ``7``.

**snip:** ``bool``
    When multi-images are encountered (for example, an animated image), this causes one of the images to be kept and drops
    the other ones. Depending on the input format, this may be either the first or the most relevant (e.g. the largest) image.

**strip:** ``bool|string``
    When set to ``true``, all extra image headers, such as its comments, EXIF markers, and other metadata, will be removed.
    Equivalently, the string value ``all`` also removes all extra metadata.

**preserve_attributes:** ``bool``
    Preserve file attributes (time stamps, file access rights, etc.) where applicable/possible.

**interlace_type:** ``int``
    Sets the interlace type used for the output file. When set to ``0``, the output image will be non-interlaced. When
    set to ``1``, the output image will be interlaced using the Adam7 method. When not set, the output will have the
    same interlace type as the original input.

**no_bit_depth_reductions:** ``bool``
    Disables any bit depth reduction optimizations.

**no_color_type_reductions:** ``bool``
    Disables any color type reduction optimizations.

**no_palette_reductions:** ``bool``
    Disables any color palette reduction optimizations.

**no_reductions:** ``bool``
    Disables any lossless reduction optimizations, enabling ``no_bit_depth_reductions``, ``no_color_type_reductions``,
    and ``no_palette_reductions``.

Parameters
----------

**liip_imagine.optipng.stripAll:** ``bool``
    Removes all comments, EXIF markers, and other metadata from the image binary.

**liip_imagine.optipng.level:** ``int``
    Sets the image optimization factor. Default is ``7``.

**liip_imagine.optipng.binary:** ``string``
    Sets the location of the ``optipng`` executable. Default is ``/usr/bin/optipng``.

**liip_imagine.optipng.tempDir:** ``string``
    Sets the directory to store temporary files.


.. tip::

    The value of ``liip_imagine.optipng.tempDir`` can be set to an in-memory mount point
    on supported operating systems, such as ``/run/shm`` on Linux. This will decrease disk
    load and may increase performance.
