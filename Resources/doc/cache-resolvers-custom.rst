
Custom Cache Resolver
=====================

You can define your own custom cache resolvers to handle cache resolution using
your storage backend. Creating a custom cache resolver begins by creating
a class that implements the ``ResolverInterface``:

.. code-block:: php

    interface ResolverInterface
    {
        public function isStored($path, $filter);
        public function resolve($path, $filter);
        public function store(BinaryInterface $binary, $path, $filter);
        public function remove(array $paths, array $filters);
    }

Once you have defined your custom cache resolver, you need to define it as a
service and tag it with ``liip_imagine.cache.resolver``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        services:
            imagine.cache.resolver.my_custom:
                class: App\Service\MyCustomResolver
                arguments:
                    - "@filesystem"
                    - "@router"
                tags:
                    - { name: "liip_imagine.cache.resolver", resolver: my_custom_cache }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="imagine.cache.resolver.my_custom" class="App\Service\MyCustomResolver">
            <tag name="liip_imagine.cache.resolver" resolver="my_custom_cache" />
            <argument type="service" id="filesystem" />
            <argument type="service" id="router" />
        </service>

.. note::

    For more information on the Service Container, reference the official
    `Symfony Service Container documentation`_.

Now your custom cache resolver can be set as the global default
using the name defined in the ``resolver`` attribute of the ``tags`` key.

.. code-block:: yaml

    liip_imagine:
        cache: my_custom_cache

Alternatively you can only set the custom cache resolver for just a specific
filter set:

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            my_special_style:
                cache: my_custom_cache


.. _`Symfony Service Container documentation`: http://symfony.com/doc/current/book/service_container.html
