
.. _data-loaders-grid-fs:

GridFS Loader
=============

The ``GridFSLoader`` allows you to load your images from `MongoDB GridFS`_.

Configuration
-------------

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_special_style:
                data_loader: grid_fs
                filters:
                    my_custom_filter: { }

Define a service for the loader:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        services:
            liip_imagine.binary.loader.grid_fs:
                class: Liip\ImagineBundle\Binary\Loader\GridFSLoader
                arguments:
                    - "@doctrine.odm.mongodb.document_manager"
                    - Application\ImageBundle\Document\Image
                tags:
                    - { name: "liip_imagine.binary.loader", loader: grid_fs }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="liip_imagine.binary.loader.grid_fs" class="Liip\ImagineBundle\Binary\Loader\GridFSLoader">
            <tag name="liip_imagine.binary.loader" loader="grid_fs" />
            <argument type="service" id="doctrine.odm.mongodb.document_manager" />
            <argument>Application\ImageBundle\Document\Image</argument>
        </service>


Usage
-----

Reference the image by its ``id`` when piping to the template helper:

.. configuration-block::

    .. code-block:: html+twig

        <img src="{{ image.id | imagine_filter('my_thumb') }}" />

    .. code-block:: html+php

        <img src="<?php echo $this['imagine']->filter($image->getId(), 'my_thumb') ?>" />


.. _`MongoDB GridFS`: http://docs.mongodb.org/manual/applications/gridfs/
