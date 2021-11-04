
.. _cache-resolver-format-extension:

FormatExtensionResolver
=======================

The ``FormatExtensionResolver`` cannot be used by itself. Instead, it is a "decorator" for
another resolver. It adds the ability to set the correct file extension when a filter converted the image format.

Configuration
-------------

To use this cache resolver, you must first define the cache resolver it will decorate.
In this example, we will use the :ref:`Web Path Resolver <cache-resolver-web-path>`.

Next, we need to define a service for this cache resolver and inject the web path cache resolver service to decorate.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.imagine.cache.format_extension:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\FormatExtensionResolver
            arguments:
                - "@acme.imagine.cache.resolver.web_path"
                - "@liip_imagine.filter.configuration"
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "format_extension" }


With this configuration, the format extension resolver will rewrite the extension to match the filter format.
For example, you have the source image ``image.png`` and you apply filter with format ``jpg`` you will get jpeg-image ``image.jpg``.

Usage
-----

After configuring ``FormatExtensionResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: format_extension


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set ``FormatExtensionResolver`` as the cache resolver for a specific
filter set using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: format_extension
                filters:
                    # the filter list
