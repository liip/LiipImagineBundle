
.. _post-processor-jpegoptim:

JPEG Optim
==========

The ``JpegOptimPostProcessor`` is a built-in post-processor that performs a number of
*lossless* optimizations on *JPEG* encoded images.

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
                    jpegoptim: { strip_all: true, max: 70, progressive: true }

This configuration enables metadata stripping and progressive JPEG encoding, and sets
a maximum quality factor of 70 for the resulting image binary.

.. note::

    The default executable path is ``/usr/bin/jpegoptim``. If installed elsewhere
    on your system, you must set the ``liip_imagine.jpegoptim.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.jpegoptim.binary: /your/custom/path/to/jpegoptim


Options
-------

**strip_all:** ``bool``
    Removes all comments, EXIF markers, and other image metadata.

**max:** ``int``
    Sets the maximum image quality factor.

**progressive:** ``bool``
    Ensures the image uses progressive encoding.


Parameters
----------

**liip_imagine.jpegoptim.stripAll:** ``bool``
    Removes all comments, EXIF markers, and other metadata from the image binary.

**liip_imagine.jpegoptim.max:** ``int``
    Assigns the maximum quality factor for the image binary.

**liip_imagine.jpegoptim.progressive:** ``bool``
    Ensures that progressive encoding is enabled for the image binary.

**liip_imagine.jpegoptim.binary:** ``string``
    Sets the location of the ``jpegoptim`` executable. Default is ``/usr/bin/jpegoptim``.

**liip_imagine.jpegoptim.tempDir:** ``string``
    Sets the directory to store temporary files.


.. tip::

    The value of ``liip_imagine.jpegoptim.tempDir`` can be set to an in-memory mount point
    on supported operating systems, such as ``/run/shm`` on Linux. This will decrease disk
    load and may increase performance.
