
.. _data-loaders-stream:

Stream Loader
=============

The ``StreamLoader`` allows you to load images using PHP Streams.


Configuration
-------------

.. code-block:: yaml

    liip_imagine:
        loaders:
            stream.profile_photos:
                stream:
                    wrapper: gaufrette://profile_photos


Custom
~~~~~~

The ``Liip\ImagineBundle\Binary\Loader\StreamLoader`` allows to read images
from any stream (http, ftp, and others…)  registered thus allowing you to serve your images from
literally anywhere.

The example service definition shows how to use a stream wrapped by the
`Gaufrette`_ filesystem abstraction layer. In order to have this example
working, you need to register the stream wrapper first, refer to the `Gaufrette`_
Documentation on how to do this.

If you are using the `KnpGaufretteBundle`_ you can make use of the
`StreamWrapper configuration`_ to register the filesystems.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.liip_imagine.binary.loader.stream.profile_photos:
            class: Liip\ImagineBundle\Binary\Loader\StreamLoader
            arguments:
                - 'gaufrette://profile_photos/'
            tags:
                - { name: 'liip_imagine.binary.loader', loader: 'stream.profile_photos' }


Usage
-----

Now you are ready to use the ``AwsS3Resolver`` by configuring the bundle.
The following example will configure the resolver as default.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        data_loader: stream.profile_photos


.. _`StreamWrapper configuration`: https://github.com/KnpLabs/KnpGaufretteBundle#stream-wrapper
.. _`Gaufrette`: https://github.com/KnpLabs/Gaufrette
.. _`KnpGaufretteBundle`: https://github.com/KnpLabs/KnpGaufretteBundle
