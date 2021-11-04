
.. _data-loaders-custom:

Custom Data Loader
==================

You can write custom data loaders that retrieve image data from any imaginable
backend. The data loader needs to implement the ``LoaderInterface``:

.. code-block:: php

    namespace Liip\ImagineBundle\Binary\Loader;

    interface LoaderInterface
    {
        public function find($path);
    }

The ``LoaderInterface`` defines the method ``find``, which is called with the
path to the image and needs to return an instance of ``BinaryInterface``.

.. warning::

    Be aware that ``$path`` can be coming from the image controller. You need
    to sanitize this parameter in your loader to avoid exposing files outside
    of your image collections.

You need to `configure a service`_ with your custom loader and tag it with
``liip_imagine.binary.loader``.

To register ``AppBundle\Imagine\Binary\Loader\MyCustomDataLoader`` with the name
``my_custom_data_loader``, you would use the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        services:
            imagine.data.loader.my_custom:
                class: AppBundle\Imagine\Binary\Loader\MyCustomDataLoader
                arguments:
                    - "@liip_imagine"
                    - "%liip_imagine.formats%"
                tags:
                    - { name: "liip_imagine.binary.loader", loader: my_custom_data_loader }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="imagine.data.loader.my_custom" class="AppBundle\Imagine\Binary\Loader\MyCustomDataLoader">
            <tag name="liip_imagine.binary.loader" loader="my_custom_data_loader" />
            <argument type="service" id="liip_imagine" />
            <argument type="parameter" id="liip_imagine.formats" />
        </service>

You can set your custom data loader by adding it to the configuration as the new default
loader:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        data_loader: my_custom_data_loader

Alternatively, you can set the custom data loader for a specific filter set:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            my_special_style:
                data_loader: my_custom_data_loader
                filters:
                    # your filters


.. _`configure a service`: http://symfony.com/doc/current/book/service_container.html
