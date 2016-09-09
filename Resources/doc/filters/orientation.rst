

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

:strong:`degree:` ``float``
    Sets the "rotation angle" that defines the degree to rotate the image. Must be a
    positive number.

.. _`BoxInterface`: http://imagine.readthedocs.io/en/latest/usage/coordinates.html#boxinterface
.. _`Imagine Library`: http://imagine.readthedocs.io/en/latest/

