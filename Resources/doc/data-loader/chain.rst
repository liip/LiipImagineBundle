
.. _data-loaders-chain:

Chain Loader
============

The ``Chain`` data loader doesn't load the image binary itself; instead
it allows for loading the image binary using any number of other
configured data loaders. For example, if you configured both a
:ref:`filesystem <data-loaders-filesystem>` and
:ref:`flysystem <data-loaders-flysystem>` data loader, this loader can
be defined to load from both in a defined order, returning the image
binary from the first that responds.

.. tip::

    This loader iterates over the data loaders in the order they are
    configured in the chain definition, returning an image binary from
    the first loader that supports the passed file path. This means if
    a file exists in more than one loader, the file will be returned
    using the first one defined in your configuration file for this
    chain loader.



Configuration
-------------

As this loader leverages any number of other configured loaders, its
configuration is relatively simple; it supports only a ``loaders``
option that accepts an array of other configured loader names:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            foo:
                filesystem:
                    # configure filesystem loader

            bar:
                flysystem:
                    # configure flysystem loader

            baz:
                stream:
                    # configure stream loader

            qux:
                chain:
                    # use the "foo", "bar", and "baz" loaders
                    loaders:
                        - foo
                        - bar
                        - baz
