

Orientation Filters
===================

.. _filter-auto-rotate:

Auto Rotate
-----------

The built-in ``auto_rotate`` filter performs orientation transformations
(which includes rotating the image). This filter does not expose any
options to configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_auto_rotate_filter"
            my_auto_rotate_filter:
                filters:

                    # use the "auto_rotate" filter
                    auto_rotate: ~

.. tip::

    This filter should be called as early as possible to get the best results.

.. caution::

    This filter requires to have exif extension installed in order to work.


.. _filter-rotate:

Rotate
------

The built-in ``rotate`` filter performs orientation transformations (specifically
image rotation). This filter exposes `rotate options`_ which may be used to
configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_rotate_filter"
            my_rotate_filter:
                filters:

                    # use the "rotate" filter
                    rotate:

                        # set the degree to rotate the image
                        angle: 90


Rotate Options
~~~~~~~~~~~~~~

**degree:** ``float``
    Sets the "rotation angle" that defines the degree to rotate the image. Must be a
    positive number.


.. _filter-flip:

Flip
----

The built-in ``flip`` filter performs orientation transformations (specifically
image flipping). This filter exposes `flip options`_ which may be used to
configure its behavior.

Example configuration:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:

            # name our filter set "my_flip_filter"
            my_flip_filter:
                filters:

                    # use the "flip" filter
                    flip:

                        # set the axis to flip on
                        axis: x


Flip Options
~~~~~~~~~~~~

**axis:** ``string``
    Sets the "flip axis" that defines the axis on which to flip the image. Valid values:
    ``x``, ``horizontal``, ``y``, ``vertical``.


.. _`BoxInterface`: http://imagine.readthedocs.io/en/latest/usage/coordinates.html#boxinterface
.. _`Imagine Library`: http://imagine.readthedocs.io/en/latest/
