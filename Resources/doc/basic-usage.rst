

Basic Usage
===========

Generally, this bundle works by applying filter sets to images from inside
a template. Your filter sets are defined within the application's configuration
file (often ``app/config/config.yml``) and are comprised of a collection of
filters, post-processors, and other optional parameters.

We'll learn more about post-processors and other available parameters later,
but for now lets focus on how to define a simple filter set comprised of a
few filters.

.. _usage-create-thumbnails:

Create Thumbnails
-----------------

Before we get started, there is a small amount of configuration needed to ensure
our :doc:`data loaders <data-loaders>` and :doc:`cache-resolvers <cache-resolvers>`
operate correctly. Use the following configuration boilerplate.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:

        # configure resolvers
        resolvers:

            # setup the default resolver
            default:

                # use the default web path
                web_path: ~

        # your filter sets are defined here
        filter_sets:

            # use the default cache configuration
            cache: ~

With the basic configuration in place, we'll start with an example that fulfills a
common use-case: creating thumbnails. Lets assume we want the resulting thumbnails
to have the following transformations applied to them:

* Scale and crop the image to 120x90px.
* Add a 2px black border to the scaled image.
* Adjust the image quality to 75.

Adding onto our boilerplate from above, we need to define a filter set (which we'll
name ``my_thumb``) with two filters configured: the ``thumbnail`` and ``background``
filters.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        resolvers:
            default:
                web_path: ~

        filter_sets:
            cache: ~

            # the name of the "filter set"
            my_thumb:

                # adjust the image quality to 75%
                quality: 75

                # list of transformations to apply (the "filters")
                filters:

                    # create a thumbnail: set size to 120x90 and use the "outbound" mode
                    # to crop the image when the size ratio of the input differs
                    thumbnail: { size: [120, 90], mode: outbound }

                    # create a 2px black border: center the thumbnail on a black background
                    # 4px larger to create a 2px border around the final image
                    background: { size: [124, 94], position: center, color: '#000000' }

You've now created a filter set called ``my_thumb`` that performs a thumbnail
transformation. The ``thumbnail`` filter sizes the image to the desired width
and height (120x90px), and its ``mode: outbound`` option causes
the resulting image to be cropped if the input ratio differs. The ``background``
filter results in a 2px black border by creating a black canvas 124x94px in size,
and positioning the thumbnail at its center.

.. note::

    A filter set can have any number of filters defined for it. Simple
    transformations may only require a single filter, while more complex
    transformations can have any number of filters defined for them.

There are a number of additional :doc:`filters <filters>`, but for now you can use
your newly defined ``my_thumb`` filter set immediately within a template.

.. configuration-block::

    .. code-block:: html+twig

        <img src="{{ asset('/relative/path/to/image.jpg') | imagine_filter('my_thumb') }}" />

    .. code-block:: html+php

        <img src="<?php echo $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb') ?>" />

Behind the scenes, the bundle applies the filter(s) to the image on-the-fly
when the first page request is served. The transformed image is then cached
for subsequent requests. The final cached image path would be similar to
``/media/cache/my_thumb/relative/path/to/image.jpg``.

.. note::

    Using the ``dev`` environment you might find that images are not properly
    rendered via the template helper. This is often caused by having
    ``intercept_redirect`` enabled in your application configuration. To ensure
    images are rendered, it is strongly suggested to disable this option:

    .. code-block:: yaml

        # app/config/config_dev.yml

        web_profiler:
            intercept_redirects: false


Runtime Options
---------------

Sometimes, you may have a filter defined that fulfills 99% of your usage
scenarios. Instead of defining a new filter for the erroneous 1% of cases,
you may instead choose to alter the behavior of a filter at runtime by
passing the template helper an options array.

.. configuration-block::

    .. code-block:: html+twig

        {% set runtimeConfig = {"thumbnail": {"size": [50, 50] }} %}

        <img src="{{ asset('/relative/path/to/image.jpg') | imagine_filter('my_thumb', runtimeConfig) }}" />

    .. code-block:: html+php

        <?php
        $runtimeConfig = [
            "thumbnail" => [
                "size" => [50, 50]
            ]
        ];
        ?>

        <img src="<?php $this['imagine']->filter('/relative/path/to/image.jpg', 'my_thumb', $runtimeConfig) ?>" />


Path Resolution
---------------

Sometimes you need to resolve the image path returned by this bundle for a
filtered image. This can easily be achieved using Symfony's console binary
or programmatically from within a controller or other piece of code.


Resolve with the Console
~~~~~~~~~~~~~~~~~~~~~~~~

You can resolve an image URL using the console command
``liip:imagine:cache:resolve``. The only required argument is one or more
relative image paths (which must be separated by a space).

.. code-block:: bash

    $ php app/console liip:imagine:cache:resolve relative/path/to/image1.jpg relative/path/to/image2.jpg

Additionally, you can use the ``--filters`` option to specify which filter
you want to resolve for (if the ``--filters`` option is omitted, all
available filters will be resolved).

.. code-block:: bash

    $ php app/console liip:imagine:cache:resolve relative/path/to/image1.jpg --filters=my_thumb


Resolve Programmatically
~~~~~~~~~~~~~~~~~~~~~~~~

You can resolve the image URL in your code using the ``getBrowserPath``
method of the ``liip_imagine.cache.manager`` service. Assuming you already
have the service assigned to a variable called ``$imagineCacheManager``,
you would run:

.. code-block:: php

    $imagineCacheManager->getBrowserPath('/relative/path/to/image.jpg', 'my_thumb');

Often, you need to perform this operation in a controller.

You can access the ``CacheManager`` simply by type hinting it in your controller method: the service
``liip_imagine.cache.manager`` will be automatically injected and you will be able to call
``getBrowserPath`` on a relative image path to get its resolved location.

.. code-block:: php

    <?php
    
    namespace App\Controller;
    
    use \Liip\ImagineBundle\Imagine\Cache\CacheManager;

    class YourController
    {
        public function yourControllerMethod(CacheManager $imagineCacheManager)
        {
            /** @var string */
            $resolvedPath = $imagineCacheManager->getBrowserPath('/relative/path/to/image.jpg', 'my_thumb');
    
            // ...
        }
    }

WebP image format
~~~~~~~~~~~~~~~~~

The WebP format better optimizes the quality and size of the compressed image
compared to JPEG and PNG. Google strongly recommends using this format. You can
set it as the default format for all images.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        default_filter_set_settings:
            format: webp

However, not all `browsers support the WebP format`_, and for compatibility with
all browsers it is recommended to return images in their original format for
those browsers that do not support WebP. This means that you need to store 2
versions of the image. One in WebP format and the other in original format.
**Remember that this almost doubles the amount of used space on the server for
storing filtered images.**

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        # configure webp
        webp:
            generate: true

        # example filter
        filter_sets:
            thumbnail_web_path:
                filters:
                    thumbnail: { size: [223, 223], mode: inset }

If browser supports WebP, the request ``https://localhost/media/cache/resolve/thumbnail_web_path/images/cats.jpeg``
will be redirected to ``https://localhost/media/cache/thumbnail_web_path/images/cats.jpeg.webp``
otherwise to ``https://localhost/media/cache/thumbnail_web_path/images/cats.jpeg``

.. note::

    Using an unsecured connection (non HTTPS) on your site can cause problems with
    caching the resolved paths for users, which can lead to the fact that users
    whose browser does not support WebP will serve a picture in WebP format.
    You can fix this problem by changing the redirect code from 301 *(Moved
    Permanently)* to 302 *(Moved Temporarily)*.

    .. code-block:: yaml

        # app/config/config.yml

        liip_imagine:
            controller:
                redirect_response_code: 302

.. _`browsers support the WebP format`: https://caniuse.com/webp
