
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

This configuration sets a quality factor range of 75 to 80 for the resulting image binary.

.. note::

    The default executable path is ``/usr/bin/pngquant``. If installed elsewhere
    on your system, you must set the ``liip_imagine.pngquant.binary`` parameter accordingly.

    .. code-block:: yaml

        # app/config/config.yml

        parameters:
            liip_imagine.pngquant.binary: /your/custom/path/to/pngquant


Options
-------

:strong:`quality:` ``int``
    Sets the image optimization factor.


Parameters
----------

:strong:`liip_imagine.pngquant.binary:` ``string``
    Sets the location of the ``pnquant`` executable. Default is ``/usr/bin/pnquant``.
