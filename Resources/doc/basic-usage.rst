Basic Usage
===========

This bundle works by configuring a set of filters and then applying those
filters to images inside a template So, start by creating some sort of filter
that you need to apply somewhere in your application. For example, suppose
you want to thumbnail an image to a size of 120x90 pixels:

.. code-block:: yaml

    # app/config/config.yml
    liip_imagine:
        resolvers:
           default:
              web_path: ~

        filter_sets:
            cache: ~
            my_thumb:
                quality: 75
                filters:
                    thumbnail: { size: [120, 90], mode: outbound }

You've now defined a filter set called ``my_thumb`` that performs a thumbnail
transformation. We'll learn more about available transformations later, but
for now, this new filter can be used immediately in a template:

.. configuration-block::

    .. code-block:: html+jinja

        <img src="{{ '/relative/path/to/image.jpg'|imagine_filter('my_thumb') }}" />

    .. code-block:: html+php

        <img src="<?php echo $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb') ?>" />

Behind the scenes, the bundles applies the filter(s) to the image on the
first request and then caches the image to a similar path. On the next request,
the cached image would be served directly from the file system.

In this example, the final rendered path would be something like
``/media/cache/my_thumb/relative/path/to/image.jpg``. This is where Imagine
would save the filtered image file.

You can also pass some options at runtime:

.. configuration-block::

    .. code-block:: html+jinja

        {% set runtimeConfig = {"thumbnail": {"size": [50, 50] }} %}
        <img src="{{ '/relative/path/to/image.jpg' | imagine_filter('my_thumb', runtimeConfig) }}" />

    .. code-block:: html+php

        <?php
        $runtimeConfig = array(
            "thumbnail" => array(
                "size" => array(50, 50)
            )
        );
        ?>

        <img src="<?php echo $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb', $runtimeConfig) ?>" />

Also you can resolve image url from console:

.. code-block:: bash

    $ php app/console liip:imagine:cache:resolve relative/path/to/image.jpg relative/path/to/image2.jpg --filters=my_thumb --filters=thumbnail_default

Where only paths required parameter. They are separated by space. If you
omit filters option will be applied all available filters.

If you need to access filtered image URL in your controller:

.. code-block:: php

    $this->get('liip_imagine.cache.manager')->getBrowserPath('/relative/path/to/image.jpg', 'my_thumb'),

In this case, the final rendered path would contain some random data in the
path ``/media/cache/my_thumb/S8rrlhhQ/relative/path/to/image.jpg``. This is where
Imagine would save the filtered image file.

.. note::

    Using the ``dev`` environment you might find that the images are not properly
    rendered when using the template helper. This is likely caused by having
    ``intercept_redirect`` enabled in your application configuration. To ensure
    that the images are rendered disable this option:

    .. code-block:: yaml

        web_profiler:
            intercept_redirects: false
