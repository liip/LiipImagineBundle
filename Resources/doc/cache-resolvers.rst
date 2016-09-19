

Cache Resolvers
===============

A number of built-in cache resolvers are available:

.. toctree::
    :maxdepth: 1
    :glob:

    cache-resolver/*


Set the Default Cache Resolver
------------------------------

The default cache is the :ref:`web path cache resolver <cache-resolver-web-path>`,
which caches images under ``/media/cache/`` within your application web root path.

You can specify the cache resolver to use per individual ``filter_sets`` or globally.
To set the default cache resolver globally, use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: your_resolver


To change the default configuration, you can redefine the default cache resolver
by explicitly defining a resolver called ``default``:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        resolvers:
            default:
                web_path:
                    cache_prefix: custom_path

To change the cache resolver for a specific ``filter_set``, use the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: your_resolver
                filters:
                    # the filter list

Custom Cache Resolver
---------------------

You can easily define your own, custom cache resolvers to handle cache resolution
using any imaginable backend. Creating a custom cache resolver begins by creating
a class that implements the ``ResolverInterface``, as shown below.

.. code-block:: php

    interface ResolverInterface
    {
        public function isStored($path, $filter);
        public function resolve($path, $filter);
        public function store(BinaryInterface $binary, $path, $filter);
        public function remove(array $paths, array $filters);
    }

The following is a template for creating your own cache resolver. You must provide
implementations for all methods to create a valid cache resolver.

.. code-block:: php

    <?php

    namespace AppBundle\Imagine\Cache\Resolver;

    use Liip\ImagineBundle\Binary\BinaryInterface;
    use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
    use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

    class MyCustomResolver implements ResolverInterface
    {
        /**
         * @param string $path
         * @param string $filter
         *
         * @return bool
         */
        public function isStored($path, $filter)
        {
            /** @todo: implement */
        }

        /**
         * @param string $path
         * @param string $filter
         *
         * @return string
         */
        public function resolve($path, $filter)
        {
            /** @todo: implement */
        }

        /**
         * @param BinaryInterface $binary
         * @param string          $path
         * @param string          $filter
         */
        public function store(BinaryInterface $binary, $path, $filter)
        {
            /** @todo: implement */
        }

        /**
         * @param string[] $paths
         * @param string[] $filters
         */
        public function remove(array $paths, array $filters)
        {
            /** @todo: implement */
        }
    }

Once you have defined your custom cache resolver, you must define it as a service and tag it
with ``liip_imagine.cache.resolver``.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml

        services:
            imagine.cache.resolver.my_custom:
                class: AppBundle\Imagine\Cache\Resolver\MyCustomResolver
                arguments:
                    - "@filesystem"
                    - "@router"
                tags:
                    - { name: "liip_imagine.cache.resolver", resolver: my_custom_cache }

    .. code-block:: xml

        <!-- app/config/services.xml -->

        <service id="imagine.cache.resolver.my_custom" class="AppBundle\Imagine\Cache\Resolver\MyCustomResolver">
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
