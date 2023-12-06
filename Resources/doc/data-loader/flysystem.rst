
.. _data-loaders-flysystem:

FlySystem Loader
================

The ``FlysystemLoader`` lets you load images using the `Flysystem`_ filesystem abstraction
layer.

Dependencies
------------

This data loader uses a ``League\\Flysystem\\Filesystem`` to load files from any source supported
by `Flysystem`_. Flysystem is provided by the ``league/flysystem`` package, but the easiest way to
set up a service is using one of the flysystem bundles. You can use either `OneupFlysystemBundle`_
or `The League FlysystemBundle`_. Both allow you to define filesystems as services,
LiipImagineBundle does not care which one you use.

To install the `OneupFlysystemBundle`_, run the following composer command:

.. code-block:: bash

    $ composer require oneup/flysystem-bundle

Configuration
-------------

The value of ``filesystem_service`` must be a service id of class ``League\\Flysystem\\Filesystem``.
The service name depends on the naming scheme of the bundle, for `The League FlysystemBundle`_, it
will be different than in the example below.

Using `OneupFlysystemBundle`_, a basic configuration might look as follows:

.. code-block:: yaml

    # /config/liip_imagine.yaml

    liip_imagine:
        loaders:
            profile_photos:
                flysystem:
                    filesystem_service: oneup_flysystem.profile_photos_filesystem
        data_loader: profile_photos


    # /config/oneup_flysystem.yaml

    oneup_flysystem:
        adapters:
            profile_photos:
                local:
                    location:  "path/to/profile/photos"

        filesystems:
            profile_photos:
                adapter: profile_photos


Using `The League FlysystemBundle`_:

.. code-block:: yaml

    # /config/liip_imagine.yaml

    liip_imagine:
        loaders:
            profile_photos:
                flysystem:
                    #⚠️ do not use the full flysystem service alias (which would be `flysystem.adapter.profile_photos.storage`) 
                    filesystem_service: 'profile_photos.storage'
        data_loader: profile_photos


    # /config/flysystem.yaml
    
    flysystem:
        storages:
            profile_photos.storage:
                adapter: 'local'
                options:
                    directory:  "path/to/profile/photos"
    


.. _`Flysystem`: https://github.com/thephpleague/flysystem
.. _`OneupFlysystemBundle`: https://github.com/1up-lab/OneupFlysystemBundle
.. _`The League FlysystemBundle`: https://github.com/thephpleague/flysystem-bundle
