
.. _post-processor-cweb:

cwebp
=====

The ``CwebpPostProcessor`` is a built-in post-processor that performs a number of optimizations on *WEBP* encoded
images.

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
                    cwebp: { metadata: 'none', q: 70 }

This configuration enables metadata stripping and sets a maximum compression factor of 70 for the resulting image
binary.

.. note::

    The default executable path is ``/usr/bin/cwebp``. If installed elsewhere
    on your system, you must set the ``liip_imagine.cwebp.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.cwebp.binary: /your/custom/path/to/cwebp


Options
-------

**q:** ``int``
    Specify the compression factor for RGB channels between ``0`` and ``100``. The default is ``75``.

**alpha_q:** ``int``
    Specify the compression factor for alpha compression between ``0`` and ``100``.

**m:** ``int``
    Specify the compression method to use. Possible values range from ``0`` to ``6``.

**alpha_filter:** ``string``
    Specify the predictive filtering method for the alpha plane. One of ``none``, ``fast`` or ``best``.

**alpha_method:** ``int``
    Specify the algorithm used for alpha compression: ``0`` or ``1``.

**exact:** ``bool``
    Preserve RGB values in transparent area. The default is off, to help compressibility.

**metadata:** ``array``
    An array of metadata to copy from the input to the output if present. Valid values: ``all``, ``none``, ``exif``,
    ``icc``, ``xmp``.



Parameters
----------

**liip_imagine.cwebp.binary:** ``string``
    Sets the location of the ``cwebp`` executable. Default is ``/usr/bin/cwebp``.

**liip_imagine.cwebp.tempDir:** ``string``
    Sets the directory to store temporary files.

**liip_imagine.cwebp.q:** ``int``
    Specify the compression factor for RGB channels between ``0`` and ``100``. The default is ``75``.

**liip_imagine.cwebp.alphaQ:** ``int``
    Specify the compression factor for alpha compression between ``0`` and ``100``.

**liip_imagine.cwebp.m:** ``int``
    Specify the compression method to use. Possible values range from ``0`` to ``6``.

**liip_imagine.cwebp.alphaFilter:** ``string``
    Specify the predictive filtering method for the alpha plane. One of ``none``, ``fast`` or ``best``.

**liip_imagine.cwebp.alphaMethod:** ``int``
    Specify the algorithm used for alpha compression: ``0`` or ``1``.

**liip_imagine.cwebp.exact:** ``bool``
    Preserve RGB values in transparent area. The default is off, to help compressibility.

**liip_imagine.cwebp.metadata:** ``array``
    An array of metadata to copy from the input to the output if present. Valid values: ``all``, ``none``, ``exif``,
    ``icc``, ``xmp``.
