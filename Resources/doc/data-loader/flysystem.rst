
.. _data-loaders-flysystem:

FlySystem Loader
================

The ``FlysystemLoader`` lets you load images using the `Flysystem`_ filesystem abstraction
layer.

Dependencies
------------

This cache resolver has a soft dependency on `OneupFlysystemBundle`_, which
can be installed by executing the following command in your project directory:

.. code-block:: bash

    $ composer require oneup/flysystem-bundle

.. note::

    This command requires that `Composer`_ is installed globally, as explained in
    their `installation documentation`_.


Configuration
-------------

Using `OneupFlysystemBundle`_, a basic configuration might look like the following.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            profile_photos:
                flysystem:
                    filesystem_service: oneup_flysystem.profile_photos_filesystem
        data_loader: profile_photos

    oneup_flysystem:
        adapters:
            profile_photos:
                local:
                    directory:  "path/to/profile/photos"

        filesystems:
            profile_photos:
                adapter: profile_photos

.. note::

    The value of ``filesystem_service`` must be a service id that returns an instance
    of ``League\\Flysystem\\Filesystem``.

.. _`Flysystem`: https://github.com/thephpleague/flysystem
.. _`OneupFlysystemBundle`: https://github.com/1up-lab/OneupFlysystemBundle
.. _`Composer`: https://getcomposer.org/
.. _`installation documentation`: https://getcomposer.org/doc/00-intro.md
