
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

:strong:`strip_all:` ``bool``
    Removes all comments, EXIF markers, and other image metadata.

:strong:`level:` ``int``
    Sets the image optimization factor.


Parameters
----------

:strong:`liip_imagine.optipng.stripAll:` ``bool``
    Removes all comments, EXIF markers, and other metadata from the image binary.

:strong:`liip_imagine.optipng.level:` ``int``
    Sets the image optimization factor. Default is ``7``.

:strong:`liip_imagine.optipng.binary:` ``string``
    Sets the location of the ``optipng`` executable. Default is ``/usr/bin/optipng``.

:strong:`liip_imagine.optipng.tempDir:` ``string``
    Sets the directory to store temporary files.


.. tip::

    The value of ``liip_imagine.optipng.tempDir`` can be set to an in-memory mount point
    on supported operating systems, such as ``/run/shm`` on Linux. This will decrease disk
    load and may increase performance.
