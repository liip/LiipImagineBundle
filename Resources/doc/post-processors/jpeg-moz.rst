
.. _post-processor-mozjpeg:

Moz JPEG
========

The ``MozJpegPostProcessor`` is a built-in post-processor that performs a number of
*safe, lossy* optimizations on *JPEG* encoded images.
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
                    mozjpeg: { quality: 70 }

This configuration sets a maximum quality factor of 70 for the resulting image binary.

.. note::

    The default executable path is ``/opt/mozjpeg/bin/cjpeg``. If installed elsewhere
    on your system, you must set the ``liip_imagine.mozjpeg.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.mozjpeg.binary: /your/custom/path/to/cjpeg


Options
-------

**quality:** ``int``
    Sets the image quality factor.


Parameters
----------

**liip_imagine.mozjpeg.binary:** ``string``
    Sets the location of the ``cjpeg`` executable. Default is ``/opt/mozjpeg/bin/cjpeg``.
